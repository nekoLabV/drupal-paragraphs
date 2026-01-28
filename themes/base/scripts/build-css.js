const { execSync } = require('child_process');
const path = require('path');

// 定义别名映射
const aliases = {
  '@library': path.resolve(__dirname, '../src/assets/scss/library'),
  '@node': path.resolve(__dirname, '../node_modules'),
  '@scss': path.resolve(__dirname, '../src/assets/scss')
};

// 构建 load-path 参数
const loadPaths = Object.values(aliases).join(':');

// 执行 sass 命令
const command = `sass --load-path=${loadPaths} src/assets/scss/index.scss dist/css/index.css`;

try {
  execSync(command, { stdio: 'inherit' });
  console.log('CSS build completed successfully!');
} catch (error) {
  console.error('CSS build failed:', error.message);
  process.exit(1);
}
