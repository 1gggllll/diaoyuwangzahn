const express = require('express');
const { prisma } = require('../prisma');
const { requireAuth } = require('../middleware/auth');

const progressRouter = express.Router();

/**
 * PUT /api/progress
 * Body: { indexId, moduleKey, status, score? }
 * status: NOT_STARTED | IN_PROGRESS | COMPLETED
 */
progressRouter.put('/', requireAuth, async (req, res) => {
  const userId = req.auth.sub;
  const { indexId, moduleKey, status, score } = req.body || {};
  if (!indexId || !moduleKey || !status) {
    return res.status(400).json({ error: 'indexId, moduleKey and status are required' });
  }
  const allowed = ['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED'];
  if (!allowed.includes(status)) {
    return res.status(400).json({ error: 'invalid status' });
  }
  const idx = await prisma.learningIndex.findFirst({
    where: { id: indexId, userId }
  });
  if (!idx) return res.status(404).json({ error: 'index not found' });

  const record = await prisma.progress.upsert({
    where: {
      userId_indexId_moduleKey: { userId, indexId, moduleKey }
    },
    create: {
      userId,
      indexId,
      moduleKey,
      status,
      score: score != null ? Number(score) : null,
      completedAt: status === 'COMPLETED' ? new Date() : null
    },
    update: {
      status,
      score: score != null ? Number(score) : undefined,
      completedAt: status === 'COMPLETED' ? new Date() : null
    }
  });

  res.json({ progress: record });
});

/**
 * GET /api/progress/:indexId
 */
progressRouter.get('/:indexId', requireAuth, async (req, res) => {
  const userId = req.auth.sub;
  const indexId = req.params.indexId;
  const idx = await prisma.learningIndex.findFirst({
    where: { id: indexId, userId }
  });
  if (!idx) return res.status(404).json({ error: 'index not found' });

  const rows = await prisma.progress.findMany({
    where: { userId, indexId }
  });
  res.json({ progress: rows });
});

module.exports = { progressRouter };
