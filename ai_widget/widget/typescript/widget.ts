// TypeScript deployment code for Craveva AI Agent
// Install: npm install --save-dev @types/node

interface CravevaConfig {
  agentId: string;
  apiKey?: string;
  apiBase?: string;
  uiConfig?: Record<string, any>;
}

function deployCravevaAgent(config: CravevaConfig): void {
    const script = document.createElement('script');
    script.src = `${config.apiBase || 'https://ai.craveva.com'}/api/v1/agents/${config.agentId}/widget.js`;
    script.crossOrigin = 'anonymous'; // Required for cross-origin CORS
    script.setAttribute('data-agent-id', config.agentId);
  
  if (config.apiKey) {
    script.setAttribute('data-api-key', config.apiKey);
  }
  
  if (config.uiConfig) {
    script.setAttribute('data-chat-config', JSON.stringify(config.uiConfig));
  }
  
  document.head.appendChild(script);
}

// Usage:
deployCravevaAgent({
  agentId: '69ccc35e7d0ece6ff702487b',
  apiKey: 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE',
  apiBase: 'https://ai.craveva.com',
  uiConfig: {
  "colors": {
    "primary": "#8b5a3c",
    "background": "#ffffff",
    "textPrimary": "#1e293b",
    "textSecondary": "#64748b",
    "userBubble": "#8b5a3c",
    "aiBubble": "#f5f3ef",
    "border": "#e2e8f0"
  },
  "typography": {
    "fontFamily": "system",
    "fontSize": "16px",
    "lineHeight": 1.5,
    "fontWeight": "400"
  },
  "layout": {
    "width": "384px",
    "height": "600px",
    "position": "bottom-right",
    "borderRadius": 12,
    "boxShadow": "0 20px 60px rgba(0, 0, 0, 0.3)",
    "zIndex": 50
  },
  "messages": {
    "userBubbleRadius": 18,
    "aiBubbleRadius": 18,
    "spacing": 16,
    "showTimestamp": true
  },
  "button": {
    "size": "medium",
    "position": "bottom-right",
    "color": "#8b5a3c",
    "visible": true
  },
  "greeting": {
    "text": "👋 Hi! How can I help you?",
    "visible": true,
    "placeholder": "Type your message..."
  }
}
});