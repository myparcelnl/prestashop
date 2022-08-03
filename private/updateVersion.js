const fs = require('fs');
const path = require('path');

const [, , version] = process.argv;

if (!version.match(/v?\d+\.\d+\.\d+(?:-(?:alpha|beta))?/)) {
  throw new Error('File must be called with version as argument.');
}

const rootDir = path.resolve(__dirname, '..');
const parsedVersion = version.replace(/^v/, '');

['composer.json', 'package.json'].forEach((file) => {
  const filePath = path.resolve(rootDir, file);
  const relativeFilePath = path.relative(rootDir, filePath);
  const contents = require(filePath);

  const oldVersion = contents.version;
  contents.version = parsedVersion;

  fs.writeFile(filePath, JSON.stringify(contents, null, 2), () => {
    // eslint-disable-next-line no-console
    console.log(
      `Changed version from \u{1b}[33m${oldVersion}\u{1b}[0m to \u{1b}[32m${parsedVersion}\u{1b}[0m in ${relativeFilePath}`,
    );
  });
});
