import fs from 'fs';
import path from 'path';

// Fix CSS and JS files
const buildDir = 'public/build/assets';
const files = fs.readdirSync(buildDir);

files.forEach(file => {
    const filePath = path.join(buildDir, file);
    
    if (file.endsWith('.css')) {
        let content = fs.readFileSync(filePath, 'utf8');
        // Replace /assets/ with /build/assets/ in CSS (avoid double replacement)
        content = content.replace(/url\(['"]?\/assets\//g, 'url(/build/assets/');
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Fixed CSS paths in ${file}`);
    } else if (file.endsWith('.js')) {
        let content = fs.readFileSync(filePath, 'utf8');
        const originalContent = content;
        
        // Replace /assets/ with /build/assets/ in JavaScript
        // First handle quoted strings: "/assets/" or '/assets/'
        content = content.replace(/(["'])\/assets\//g, '$1/build/assets/');
        
        // Then handle unquoted /assets/ (but not /build/assets/)
        // Simple approach: replace /assets/ that's not part of /build/assets/
        content = content.split('/build/assets/').join('__TEMP_BUILD_ASSETS__');
        content = content.replace(/\/assets\//g, '/build/assets/');
        content = content.split('__TEMP_BUILD_ASSETS__').join('/build/assets/');
        
        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`Fixed JS paths in ${file}`);
        }
    }
});

console.log('All asset paths fixed!');
