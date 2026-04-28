<!-- Vue Component for Craveva AI Agent -->
<!-- Install: npm install vue -->
<!-- Usage: <CravevaAgent :agent-id="69ccc35e7d0ece6ff702487b" :api-key="cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE" /> -->

<template>
  <!-- Component doesn't render anything, just loads the widget -->
</template>

<script>
export default {
  name: 'CravevaAgent',
  props: {
    agentId: {
      type: String,
      default: '69ccc35e7d0ece6ff702487b'
    },
    apiKey: {
      type: String,
      default: 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE'
    },
    apiBase: {
      type: String,
      default: 'https://ai.craveva.com'
    },
    uiConfig: {
      type: Object,
      default: () => ({
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
})
    }
  },
  mounted() {
    this.loadWidget();
  },
  beforeUnmount() {
    this.destroyWidget();
  },
  methods: {
    loadWidget() {
      // Check if script already loaded
      const existingScript = document.querySelector(`script[data-agent-id="${this.agentId}"]`);
      if (existingScript) return;

      const script = document.createElement('script');
      script.src = `${this.apiBase}/api/v1/agents/${this.agentId}/widget.js`;
      script.setAttribute('data-agent-id', this.agentId);
      
      if (this.apiKey) {
        script.setAttribute('data-api-key', this.apiKey);
      }
      
      if (Object.keys(this.uiConfig).length > 0) {
        script.setAttribute('data-chat-config', JSON.stringify(this.uiConfig));
      }
      
      document.head.appendChild(script);
    },
    destroyWidget() {
      const script = document.querySelector(`script[data-agent-id="${this.agentId}"]`);
      const chatWindow = document.getElementById('craveva-chat-window');
      const chatButton = document.querySelector('.craveva-chat-button');
      
      if (script) script.remove();
      if (chatWindow) chatWindow.remove();
      if (chatButton) chatButton.remove();
    }
  }
};
</script>