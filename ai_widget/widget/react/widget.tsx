// React Component for Craveva AI Agent
// Install: npm install react react-dom
// Usage: <CravevaAgent agentId="69ccc35e7d0ece6ff702487b" apiKey="cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE" />

import React, { useEffect } from 'react';

interface CravevaAgentProps {
  agentId: string;
  apiKey?: string;
  apiBase?: string;
  uiConfig?: Record<string, any>;
}

export const CravevaAgent: React.FC<CravevaAgentProps> = ({
  agentId = '69ccc35e7d0ece6ff702487b',
  apiKey = 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE',
  apiBase = 'https://ai.craveva.com',
  uiConfig = {
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
}) => {
  useEffect(() => {
    // Check if script already loaded
    const existingScript = document.querySelector(`script[data-agent-id="${agentId}"]`);
    if (existingScript) return;

    const script = document.createElement('script');
    script.src = `${apiBase}/api/v1/agents/${agentId}/widget.js`;
    script.crossOrigin = 'anonymous'; // Required for cross-origin CORS
    script.setAttribute('data-agent-id', agentId);
    
    if (apiKey) {
      script.setAttribute('data-api-key', apiKey);
    }
    
    if (Object.keys(uiConfig).length > 0) {
      script.setAttribute('data-chat-config', JSON.stringify(uiConfig));
    }
    
    document.head.appendChild(script);

    // Cleanup on unmount
    return () => {
      const scriptToRemove = document.querySelector(`script[data-agent-id="${agentId}"]`);
      const chatWindow = document.getElementById('craveva-chat-window');
      const chatButton = document.querySelector('.craveva-chat-button');
      
      if (scriptToRemove) scriptToRemove.remove();
      if (chatWindow) chatWindow.remove();
      if (chatButton) chatButton.remove();
    };
  }, [agentId, apiKey, apiBase, uiConfig]);

  return null; // This component doesn't render anything
};

export default CravevaAgent;