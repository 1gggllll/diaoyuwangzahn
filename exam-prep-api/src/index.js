require('dotenv').config();

const express = require('express');
const cors = require('cors');
const morgan = require('morgan');
const { config } = require('./config');
const { authRouter } = require('./routes/auth');
const { indexRouter } = require('./routes/index');
const { progressRouter } = require('./routes/progress');
const { summaryRouter } = require('./routes/summary');

const app = express();

app.use(morgan('dev'));
app.use(cors({ origin: config.corsOrigin, credentials: true }));
app.use(express.json({ limit: '1mb' }));

app.get('/health', (_req, res) => {
  res.json({ ok: true, service: 'exam-prep-api' });
});

app.use('/api/auth', authRouter);
app.use('/api/index', indexRouter);
app.use('/api/progress', progressRouter);
app.use('/api/summary', summaryRouter);

app.use((err, _req, res, _next) => {
  console.error(err);
  res.status(500).json({ error: 'Internal server error' });
});

app.listen(config.port, () => {
  console.log(`exam-prep-api listening on http://localhost:${config.port}`);
});
