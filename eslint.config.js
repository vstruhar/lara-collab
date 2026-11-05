import js from '@eslint/js';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';

const compilerRules = Object.fromEntries(
  Object.keys(reactHooks.configs.flat.recommended.rules)
    .filter(rule => rule.startsWith('react-hooks/') && rule !== 'react-hooks/rules-of-hooks')
    .map(rule => [rule, 'warn'])
);

export default [
  {
    ignores: ['resources/js/ziggy.js'],
  },
  {
    files: ['resources/js/**/*.{js,jsx}'],
    plugins: {
      react: react,
      'react-hooks': reactHooks,
    },
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      parserOptions: {
        ecmaFeatures: {
          jsx: true,
        },
      },
      globals: {
        ...globals.browser,
        route: 'readonly',
        can: 'readonly',
      },
    },
    settings: {
      react: {
        version: '19.2',
      },
    },
    rules: {
      ...js.configs.recommended.rules,
      ...react.configs.flat.recommended.rules,
      ...react.configs.flat['jsx-runtime'].rules,
      ...reactHooks.configs.flat.recommended.rules,
      ...compilerRules,
      'react/prop-types': 'off',
      'react-hooks/exhaustive-deps': 'off',
    },
  },
];
