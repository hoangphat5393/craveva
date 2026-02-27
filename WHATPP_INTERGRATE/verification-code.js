// Webhook Verification Code
// Use this code to verify your webhook endpoint

const VERIFICATION_CODE = 'VERIFY_CODE';

// Example verification function
function verifyWebhook(request, response) {
  const providedCode = request.headers['x-verification-code'] || request.body.verification_code;
  
  if (providedCode === VERIFICATION_CODE) {
    return true;
  }
  
  return false;
}

module.exports = { VERIFICATION_CODE, verifyWebhook };
