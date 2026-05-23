<script>
  (function() {
    // Prevent duplicate initialization
    if (window.cravevaWidgetLoaderInitialized) {
    console.warn('Craveva widget loader already initialized');
  return;
    }
  window.cravevaWidgetLoaderInitialized = true;

  const agentId = '69ccc35e7d0ece6ff702487b';
  const apiBase = 'https://ai.craveva.com';
  const widgetUrl = apiBase + '/api/v1/agents/' + agentId + '/widget.js';

  // Check if script already exists
  const existingScript = document.querySelector('script[data-agent-id="' + agentId + '"]');
  if (existingScript) {
    console.log('Craveva widget script already loaded');
  return;
    }

  // Create script element
  const cravevaScript = document.createElement('script');
  cravevaScript.src = widgetUrl;
  cravevaScript.crossOrigin = 'anonymous'; // Required for cross-origin CORS
  cravevaScript.setAttribute('data-agent-id', agentId);
  cravevaScript.setAttribute('data-api-key', 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE');
  cravevaScript.setAttribute("data-chat-config", "{\"colors\":{\"primary\":\"#8b5a3c\",\"background\":\"#ffffff\",\"textPrimary\":\"#1e293b\",\"textSecondary\":\"#64748b\",\"userBubble\":\"#8b5a3c\",\"aiBubble\":\"#f5f3ef\",\"border\":\"#e2e8f0\"},\"typography\":{\"fontFamily\":\"system\",\"fontSize\":\"16px\",\"lineHeight\":1.5,\"fontWeight\":\"400\"},\"layout\":{\"width\":\"384px\",\"height\":\"600px\",\"position\":\"bottom-right\",\"borderRadius\":12,\"boxShadow\":\"0 20px 60px rgba(0, 0, 0, 0.3)\",\"zIndex\":50},\"messages\":{\"userBubbleRadius\":18,\"aiBubbleRadius\":18,\"spacing\":16,\"showTimestamp\":true},\"button\":{\"size\":\"medium\",\"position\":\"bottom-right\",\"color\":\"#8b5a3c\",\"visible\":true},\"greeting\":{\"text\":\"👋 Hi! How can I help you?\",\"visible\":true,\"placeholder\":\"Type your message...\"}}");

  // Add error handling
  cravevaScript.onerror = function(error) {
    console.error('Craveva Widget: Failed to load widget script from', widgetUrl);
  console.error('Craveva Widget: Error details:', error);
  console.error('Craveva Widget: Possible causes:');
  console.error('  1. Deployment not enabled for this agent');
  console.error('  2. CORS configuration issue');
  console.error('  3. Network connectivity problem');
  console.error('  4. Invalid agent ID or API endpoint');
  console.error('Craveva Widget: Please check:');
  console.error('  - Agent ID:', agentId);
  console.error('  - API Base:', apiBase);
  console.error('  - Widget URL:', widgetUrl);
  console.error('Craveva Widget: For diagnostic help, open browser console and check network tab');
    };

  cravevaScript.onload = function() {
    console.log('Craveva Widget: Script loaded successfully from', widgetUrl);
    };

  // Add to head
  if (document.head) {
    document.head.appendChild(cravevaScript);
    } else {
    // Wait for head to be available
    document.addEventListener('DOMContentLoaded', function () {
      if (document.head) {
        document.head.appendChild(cravevaScript);
      }
    });
    }
  })();
</script>
