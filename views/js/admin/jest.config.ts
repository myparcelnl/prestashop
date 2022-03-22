import { InitialOptions } from '@jest/types/build/Config';

const jestConfig: InitialOptions = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  collectCoverageFrom: ['<rootDir>/src/**/*'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  setupFiles: ['<rootDir>/test/setup-vue.ts'],
};

export default jestConfig;
