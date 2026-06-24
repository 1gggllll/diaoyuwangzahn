const { config } = require('../config');

/**
 * @param {{ messages: { role: string, content: string }[], temperature?: number }} opts
 */
async function chatCompletion({ messages, temperature = 0.7 }) {
  if (!config.deepseek.apiKey) {
    throw new Error('DEEPSEEK_API_KEY is not set');
  }

  const res = await fetch(`${config.deepseek.baseUrl}/v1/chat/completions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${config.deepseek.apiKey}`
    },
    body: JSON.stringify({
      model: config.deepseek.model,
      messages,
      temperature,
      response_format: { type: 'json_object' }
    })
  });

  if (!res.ok) {
    const errText = await res.text();
    throw new Error(`DeepSeek API error ${res.status}: ${errText}`);
  }

  const data = await res.json();
  const content = data.choices?.[0]?.message?.content;
  if (!content) throw new Error('Empty response from DeepSeek');
  return { content, raw: data };
}

module.exports = { chatCompletion };
