// Angular Component for Craveva AI Agent
// Install: ng add @angular/core
// Usage: <app-craveva-agent [agentId]="69ccc35e7d0ece6ff702487b" [apiKey]="cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE"></app-craveva-agent>

import { Component, Input, OnInit, OnDestroy } from '@angular/core';

@Component({
  selector: 'app-craveva-agent',
  template: '<!-- Component doesn't render anything, just loads the widget -->'
})
export class CravevaAgentComponent implements OnInit, OnDestroy {
  @Input() agentId: string = '69ccc35e7d0ece6ff702487b';
  @Input() apiKey: string = 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE';
  @Input() apiBase: string = 'https://ai.craveva.com';
  @Input() uiConfig: any = {
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
};

  ngOnInit(): void {
    this.loadWidget();
  }

  ngOnDestroy(): void {
    this.destroyWidget();
  }

  private loadWidget(): void {
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
  }

  private destroyWidget(): void {
    const script = document.querySelector(`script[data-agent-id="${this.agentId}"]`);
    const chatWindow = document.getElementById('craveva-chat-window');
    const chatButton = document.querySelector('.craveva-chat-button');
    
    if (script) script.remove();
    if (chatWindow) chatWindow.remove();
    if (chatButton) chatButton.remove();
  }
}