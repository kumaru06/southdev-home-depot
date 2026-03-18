const fs = require('fs');
const mermaid = require('mermaid');
const { JSDOM } = require('jsdom');

async function render(inputPath, outputPath) {
  const mmd = fs.readFileSync(inputPath, 'utf8');
  const initMatch = mmd.match(/^%%\{init:\s*(\{[\s\S]*?\})\}\%%/);
  let config = {};
  let diagram = mmd;
  if (initMatch) {
    try {
      config = JSON.parse(initMatch[1]);
      diagram = mmd.replace(initMatch[0], '').trim();
    } catch (e) {
      // ignore parse errors and fallback
    }
  }

  const dom = new JSDOM('<div id="container"></div>', { pretendToBeVisual: true });
  global.window = dom.window;
  global.document = dom.window.document;

  // Initialize mermaid
  try {
    if (mermaid.initialize) mermaid.initialize(config || {});
    if (mermaid.mermaidAPI && mermaid.mermaidAPI.initialize) mermaid.mermaidAPI.initialize(config || {});
  } catch (e) {
    // ignore
  }

  function renderDiagram() {
    return new Promise((resolve, reject) => {
      try {
        // mermaid.render signature differs between versions
        if (mermaid.render) {
          // mermaid.render(id, text, cb?)
          const maybe = mermaid.render('graphDiv', diagram, (svgCode) => resolve(svgCode));
          if (typeof maybe === 'string') resolve(maybe);
        } else if (mermaid.mermaidAPI && mermaid.mermaidAPI.render) {
          mermaid.mermaidAPI.render('graphDiv', diagram, (svgCode) => resolve(svgCode));
        } else {
          reject(new Error('No render function found in mermaid package'));
        }
      } catch (err) {
        reject(err);
      }
    });
  }

  const svg = await renderDiagram();
  fs.writeFileSync(outputPath, svg, 'utf8');
  console.log('WROTE', outputPath);
}

const inP = process.argv[2];
const outP = process.argv[3];
if (!inP || !outP) {
  console.error('Usage: node render_mermaid_svg.js <in.mmd> <out.svg>');
  process.exit(1);
}

render(inP, outP).catch(err => {
  console.error('ERROR_RENDER', err && err.stack || err);
  process.exit(2);
});
