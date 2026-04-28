// Node.js/Express Integration for Craveva AI Agent
// Install: npm install axios

const axios = require('axios');

class CravevaAgent {
  constructor(agentId = '69ccc35e7d0ece6ff702487b', apiKey = 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE', baseUrl = 'https://ai.craveva.com') {
    this.agentId = agentId;
    this.apiKey = apiKey;
    this.baseUrl = baseUrl;
    this.apiEndpoint = `${baseUrl}/api/v1/agents/${agentId}/chat`;
  }

  /**
   * Send a message to the agent
   * @param {string} message - The message to send
   * @param {Object} options - Optional parameters (userId, companyId, outletId)
   * @returns {Promise<Object>} Response from the agent
   */
  async chat(message, options = {}) {
    const { userId, companyId, outletId } = options;

    const headers = {
      'Content-Type': 'application/json',
    };

    if (this.apiKey) {
      headers['Authorization'] = `Bearer ${this.apiKey}`;
    }

    const payload = {
      message,
    };

    if (userId) payload.user_id = userId;
    if (companyId) payload.company_id = companyId;
    if (outletId) payload.outlet_id = outletId;

    try {
      const response = await axios.post(this.apiEndpoint, payload, {
        headers,
        timeout: 30000,
      });

      return response.data;
    } catch (error) {
      console.error('Error communicating with agent:', error.message);
      return {
        error: error.message,
        output: 'Sorry, I could not process that request.',
      };
    }
  }

  /**
   * Stream chat responses (if supported)
   * @param {string} message - The message to send
   * @param {Function} onChunk - Callback for each chunk
   * @returns {Promise<void>}
   */
  async streamChat(message, onChunk) {
    // Implementation for streaming would go here
    const response = await this.chat(message);
    if (onChunk) {
      onChunk(response);
    }
    return response;
  }
}

// Usage example:
// const agent = new CravevaAgent(
//   '69ccc35e7d0ece6ff702487b',
//   'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE',
//   'https://ai.craveva.com'
// );
//
// agent.chat('Hello, how can you help me?')
//   .then(response => {
//     console.log('Agent response:', response.output);
//   })
//   .catch(error => {
//     console.error('Error:', error);
//   });

module.exports = CravevaAgent;
