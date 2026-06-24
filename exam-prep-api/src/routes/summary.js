const express = require('express');
const { prisma } = require('../prisma');
const { requireAuth } = require('../middleware/auth');
const { chatCompletion } = require('../services/deepseek');

const summaryRouter = express.Router();

/**
 * POST /api/summary/evaluate
 * Body: { indexId }
 */
summaryRouter.post('/evaluate', requireAuth, async (req, res) => {
  const userId = req.auth.sub;
  const { indexId } = req.body || {};
  if (!indexId) return res.status(400).json({ error: 'indexId required' });

  const index = await prisma.learningIndex.findFirst({
    where: { id: indexId, userId },
    include: {
      knowledgeNodes: { orderBy: { sortOrder: 'asc' } },
      quizQuestions: true,
      progress: true
    }
  });
  if (!index) return res.status(404).json({ error: 'index not found' });
  if (!process.env.DEEPSEEK_API_KEY) {
    return res.status(503).json({ error: 'DEEPSEEK_API_KEY not configured' });
  }

  const nodeSummary = index.knowledgeNodes.map((n) => ({
    title: n.title,
    difficulty: n.difficulty,
    estimatedMinutes: n.estimatedMinutes
  }));
  const progressSummary = index.progress.map((p) => ({
    moduleKey: p.moduleKey,
    status: p.status,
    score: p.score
  }));

  const system = `You are a learning coach. Respond in valid JSON only:
{
  "overallScore": number 0-100,
  "strengths": string[],
  "weaknesses": string[],
  "recommendations": string[],
  "summaryText": string (2-3 paragraphs in Chinese)
}`;

  const user = `Learning goal: ${index.goalText}
Current level: ${index.currentLevel}
Planned hours per week: ${index.targetHoursPerWeek}
Knowledge nodes: ${JSON.stringify(nodeSummary)}
Progress records: ${JSON.stringify(progressSummary)}
Quiz count: ${index.quizQuestions.length}`;

  let parsed;
  try {
    const { content } = await chatCompletion({
      messages: [
        { role: 'system', content: system },
        { role: 'user', content: user }
      ],
      temperature: 0.4
    });
    const match = content.match(/\{[\s\S]*\}/);
    parsed = match ? JSON.parse(match[0]) : JSON.parse(content);
  } catch (e) {
    console.error(e);
    return res.status(502).json({ error: 'Failed to evaluate from model', detail: String(e.message) });
  }

  const record = await prisma.studyRecord.upsert({
    where: { indexId },
    create: {
      userId,
      indexId,
      summaryJson: parsed,
      evaluatedAt: new Date()
    },
    update: {
      summaryJson: parsed,
      evaluatedAt: new Date()
    }
  });

  res.json({ evaluation: parsed, record });
});

/**
 * GET /api/summary/:indexId
 */
summaryRouter.get('/:indexId', requireAuth, async (req, res) => {
  const userId = req.auth.sub;
  const record = await prisma.studyRecord.findFirst({
    where: { indexId: req.params.indexId, userId }
  });
  if (!record) return res.status(404).json({ error: 'no summary yet' });
  res.json({ record });
});

module.exports = { summaryRouter };
