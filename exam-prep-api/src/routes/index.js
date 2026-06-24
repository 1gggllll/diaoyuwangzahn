const express = require('express');
const { z } = require('zod');
const { prisma } = require('../prisma');
const { requireAuth } = require('../middleware/auth');
const { chatCompletion } = require('../services/deepseek');

const indexRouter = express.Router();
indexRouter.use(requireAuth);

const createIndexSchema = z.object({
  goalText: z.string().min(5),
  currentLevel: z.string().min(1),
  targetHoursPerWeek: z.number().int().min(1).max(80).optional()
});

indexRouter.post('/', async (req, res) => {
  const parsed = createIndexSchema.safeParse(req.body);
  if (!parsed.success) {
    return res.status(400).json({ error: parsed.error.flatten() });
  }
  const userId = req.auth.sub;
  const { goalText, currentLevel, targetHoursPerWeek } = parsed.data;

  const index = await prisma.learningIndex.create({
    data: {
      userId,
      goalText,
      currentLevel,
      targetHoursPerWeek: targetHoursPerWeek ?? 10,
      status: 'DRAFT'
    }
  });

  res.status(201).json({ index });
});

indexRouter.get('/', async (req, res) => {
  const userId = req.auth.sub;
  const list = await prisma.learningIndex.findMany({
    where: { userId },
    orderBy: { updatedAt: 'desc' },
    include: {
      _count: { select: { knowledgeNodes: true, quizQuestions: true } }
    }
  });
  res.json({ indexes: list });
});

indexRouter.get('/:id', async (req, res) => {
  const userId = req.auth.sub;
  const index = await prisma.learningIndex.findFirst({
    where: { id: req.params.id, userId },
    include: {
      knowledgeNodes: { orderBy: { sortOrder: 'asc' } },
      quizQuestions: true,
      progress: true,
      studyRecord: true
    }
  });
  if (!index) return res.status(404).json({ error: 'Index not found' });
  res.json({ index });
});

/**
 * POST /api/index/:id/generate
 * DeepSeek generates knowledge tree + quiz for this learning index.
 */
indexRouter.post('/:id/generate', async (req, res) => {
  const userId = req.auth.sub;
  const indexId = req.params.id;
  const index = await prisma.learningIndex.findFirst({
    where: { id: indexId, userId }
  });
  if (!index) return res.status(404).json({ error: 'Index not found' });

  if (!process.env.DEEPSEEK_API_KEY) {
    return res.status(503).json({ error: 'DEEPSEEK_API_KEY not configured' });
  }

  const system = `You are an expert graduate exam (考研) study planner. Output valid JSON only with this shape:
{
  "knowledgeNodes": [
    { "title": string, "description": string, "difficulty": "EASY"|"MEDIUM"|"HARD", "estimatedMinutes": number, "sortOrder": number, "parentTitle": string|null }
  ],
  "quizQuestions": [
    { "nodeTitle": string, "question": string, "options": { "A": string, "B": string, "C": string, "D": string }, "correctAnswer": "A"|"B"|"C"|"D", "explanation": string }
  ]
}
Create 8-15 knowledge nodes and 5-10 quiz questions in Chinese, aligned with the student's goal.`;

  const user = `考研目标：${index.goalText}
当前水平：${index.currentLevel}
每周可学习小时：${index.targetHoursPerWeek}`;

  let parsed;
  try {
    const { content } = await chatCompletion({
      messages: [
        { role: 'system', content: system },
        { role: 'user', content: user }
      ],
      temperature: 0.6
    });
    parsed = JSON.parse(content);
  } catch (e) {
    console.error(e);
    return res.status(502).json({ error: 'Failed to generate from DeepSeek', detail: String(e.message) });
  }

  await prisma.$transaction(async (tx) => {
    await tx.knowledgeNode.deleteMany({ where: { indexId } });
    await tx.quizQuestion.deleteMany({ where: { indexId } });

    const titleToId = new Map();
    const nodes = parsed.knowledgeNodes || [];
    for (let i = 0; i < nodes.length; i++) {
      const n = nodes[i];
      const created = await tx.knowledgeNode.create({
        data: {
          indexId,
          title: n.title,
          description: n.description || null,
          difficulty: n.difficulty || 'MEDIUM',
          estimatedMinutes: n.estimatedMinutes ?? 60,
          sortOrder: n.sortOrder ?? i,
          parentId: null
        }
      });
      titleToId.set(n.title, created.id);
    }
    for (const n of nodes) {
      if (n.parentTitle && titleToId.has(n.parentTitle) && titleToId.has(n.title)) {
        await tx.knowledgeNode.update({
          where: { id: titleToId.get(n.title) },
          data: { parentId: titleToId.get(n.parentTitle) }
        });
      }
    }

    for (const q of parsed.quizQuestions || []) {
      await tx.quizQuestion.create({
        data: {
          indexId,
          nodeId: q.nodeTitle ? titleToId.get(q.nodeTitle) ?? null : null,
          question: q.question,
          options: q.options,
          correctAnswer: q.correctAnswer,
          explanation: q.explanation || null
        }
      });
    }

    await tx.learningIndex.update({
      where: { id: indexId },
      data: { status: 'READY' }
    });
  });

  const full = await prisma.learningIndex.findFirst({
    where: { id: indexId },
    include: {
      knowledgeNodes: { orderBy: { sortOrder: 'asc' } },
      quizQuestions: true
    }
  });

  res.json({ index: full });
});

module.exports = { indexRouter };
