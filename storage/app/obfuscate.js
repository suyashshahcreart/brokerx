import JavaScriptObfuscator from 'javascript-obfuscator';
import fs from 'fs';

const filePath = process.argv[2];

// Read the JS file instead of getting it as a command argument
const input = fs.readFileSync(filePath, 'utf8');

const result = JavaScriptObfuscator.obfuscate(input, {
  compact: true,
  controlFlowFlattening: true,
  deadCodeInjection: true,
  identifierNamesGenerator: 'hexadecimal',
  numbersToExpressions: true,
  selfDefending: true,
  stringArray: true,
  stringArrayEncoding: ['base64'],
});
console.log(result.getObfuscatedCode());