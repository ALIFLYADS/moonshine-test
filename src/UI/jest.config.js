export default {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.tsx?$': 'babel-jest',
    '^.+\\.jsx?$': 'babel-jest',
    '^.+\\.m?[jt]sx?$': 'babel-jest',
  },
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx'],
  roots: ['resources/js'],
  collectCoverage: true,
  collectCoverageFrom: ['<rootDir>/src/**/*.{js,ts,jsx,tsx}'],
  coverageDirectory: '<rootDir>/coverage',
}
