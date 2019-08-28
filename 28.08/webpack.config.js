let path = require('path');

let conf = {
  entry: './src/js/script.js',
  output: {
    path: path.resolve(__dirname, './js'),
    filename: 'main.js',
    publicPath: 'js/'
  },
  devServer: {
    overlay: true
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: ["@babel/plugin-proposal-class-properties"]
          }
        }
      },
      {
        test: /\.css$/,
        use: [
          "style-loader",
          "css-loader"
        ]
      }
    ]
  }
};

module.exports = (env, options) => {
  conf.devtool = options.mode === "production" ?
    false :
    "cheap-module-eval-source-map";
  return conf;
};