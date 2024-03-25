const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { VueLoaderPlugin } = require('vue-loader');

module.exports = [
  // Frontend Configuration
  {
    mode: 'development',
    entry: {
      frontend: './js/frontend/entry.js',
      backend: './js/backend/entry.js',
    },
    output: {
      path: path.resolve(__dirname, 'js/dist'),
      filename: '[name].bundle.js',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
            },
          },
        },
        {
          test: /\.vue$/,
          exclude: /node_modules/,
          use: {
            loader: 'vue-loader',
          },
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [
                    require('tailwindcss'),
                    require('autoprefixer'),
                  ],
                },
              },
            },
          ],
        },
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader',
            'sass-loader',
          ],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'styles.css',
      }),
      new VueLoaderPlugin(),
    ],
  },
];
