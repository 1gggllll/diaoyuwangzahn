# 考研学习网站后端 API

Node.js + Express + Prisma + PostgreSQL，集成 DeepSeek 生成学习索引、测验与学习总结。

## 功能

| 模块 | 说明 |
|------|------|
| 用户认证 | 注册 / 登录 / JWT |
| 学习索引 | 创建考研目标，调用 DeepSeek 生成知识树与测验题 |
| 学习进度 | 按模块记录 NOT_STARTED / IN_PROGRESS / COMPLETED |
| 学习总结 | DeepSeek 评估进度并生成中文报告 |

## 快速开始

```bash
cd exam-prep-api
cp .env.example .env
# 编辑 .env：DATABASE_URL、JWT_SECRET、DEEPSEEK_API_KEY

npm install
npm run db:push
npm run dev
```

服务默认：`http://localhost:3000`

## API 一览

### 认证

```http
POST /api/auth/register
{ "email": "a@b.com", "password": "12345678", "displayName": "张三" }

POST /api/auth/login
{ "email": "a@b.com", "password": "12345678" }

GET /api/auth/me
Authorization: Bearer <token>
```

### 学习索引

```http
POST /api/index
{ "goalText": "2027考研计算机408", "currentLevel": "本科大二", "targetHoursPerWeek": 15 }

GET /api/index
GET /api/index/:id

POST /api/index/:id/generate   # 调用 DeepSeek 生成知识节点 + 测验
```

### 进度

```http
PUT /api/progress
{ "indexId": "...", "moduleKey": "node-xxx", "status": "COMPLETED", "score": 85 }

GET /api/progress/:indexId
```

### 总结

```http
POST /api/summary/evaluate
{ "indexId": "..." }

GET /api/summary/:indexId
```

## 环境变量

| 变量 | 说明 |
|------|------|
| `DATABASE_URL` | PostgreSQL 连接串 |
| `JWT_SECRET` | JWT 签名密钥 |
| `DEEPSEEK_API_KEY` | DeepSeek API Key |
| `DEEPSEEK_BASE_URL` | 默认 `https://api.deepseek.com` |
| `DEEPSEEK_MODEL` | 默认 `deepseek-chat` |
| `CORS_ORIGIN` | 前端地址，如 `http://localhost:5173` |

## 数据库

使用 Prisma。模型：`User`、`LearningIndex`、`KnowledgeNode`、`QuizQuestion`、`Progress`、`StudyRecord`。

```bash
npm run db:migrate   # 生产迁移
npm run db:studio    # 可视化管理
```

## 技术栈

- Express 4
- Prisma 5 + PostgreSQL
- JWT + bcrypt
- DeepSeek Chat Completions（JSON 输出）
