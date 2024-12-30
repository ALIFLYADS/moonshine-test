export default {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.tsx?$': 'babel-jest',
    '^.+\\.jsx?$': 'babel-jest',
    '^.+\\.m?[jt]sx?$': 'babel-jest',
  },
  testPathIgnorePatterns: ['/coverage/'],
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx'],
  roots: ['resources/js'],
  collectCoverage: true,
  collectCoverageFrom: ['<rootDir>/resources/js/**/*.{js,ts,jsx,tsx}'],
  coverageDirectory: '<rootDir>/resources/js/__tests__/coverage',
}
