import request from '../Core.js'
import {ComponentRequestData} from '../../DTOs/ComponentRequestData.js'
import axios from 'axios'
import MockAdapter from 'axios-mock-adapter'
import {afterEach, beforeEach, describe, expect, jest, it} from '@jest/globals'

// Mock global objects and functions
global.MoonShine = {
  ui: {
    toast: jest.fn(), // Mock the toast function
  },
  callbacks: {},
}

// Mock DOM API
document.querySelectorAll = jest.fn()
document.querySelector = jest.fn()

describe('request function', () => {
  let mockAxios // For mocking axios requests
  let t

  beforeEach(() => {
    // Set up axios mock
    mockAxios = new MockAdapter(axios)

    // Reset mocks
    jest.clearAllMocks()

    // Mock component-like object
    t = {
      $el: {},
      loading: true,
    }
  })

  afterEach(() => {
    mockAxios.reset()
  })

  it('should return if url is not provided', () => {
    request(t, '')
    expect(t.loading).toBe(false)
    expect(MoonShine.ui.toast).toHaveBeenCalledWith('Request URL not set', 'error')
  })

  it('should display an error if offline', () => {
    jest.spyOn(navigator, 'onLine', 'get').mockReturnValueOnce(false)
    request(t, '/test-url')
    expect(t.loading).toBe(false)
    expect(MoonShine.ui.toast).toHaveBeenCalledWith('No internet connection', 'error')
  })

  it('should instantiate ComponentRequestData if not provided', () => {
    const componentRequestData = null
    request(t, '/test-url', 'get', {}, {}, componentRequestData)
    expect(MoonShine.ui.toast).not.toHaveBeenCalled() // No error toast
  })

  it('should call beforeRequest if specified', () => {
    const componentRequestData = new ComponentRequestData().withBeforeRequest('testCallback')
    jest.spyOn(componentRequestData, 'hasBeforeRequest').mockReturnValueOnce(true)
    MoonShine.callbacks.testCallback = jest.fn()

    request(t, '/test-url', 'get', {}, {}, componentRequestData)

    expect(componentRequestData.hasBeforeRequest).toHaveBeenCalled()
    expect(MoonShine.callbacks.testCallback).toHaveBeenCalledWith(t.$el, t)
  })

  it('should handle successful axios response', async () => {
    const componentRequestData = new ComponentRequestData()
    mockAxios.onGet('/test-url').reply(200, {message: 'Success'})

    await request(t, '/test-url', 'get', {}, {}, componentRequestData)

    expect(t.loading).toBe(false) // Loading should be false after response
    expect(MoonShine.ui.toast).toHaveBeenCalledWith('Success', 'success') // Show success toast
  })

  it('should handle fields_values in response', async () => {
    const componentRequestData = new ComponentRequestData()
    mockAxios.onGet('/test-url').reply(200, {
      fields_values: {'#input': 'value'},
    })

    document.querySelector.mockReturnValueOnce({value: '', dispatchEvent: jest.fn()})

    await request(t, '/test-url', 'get', {}, {}, componentRequestData)

    expect(document.querySelector).toHaveBeenCalledWith('#input')
  })

  it('should handle redirects in response', async () => {
    delete window.location
    window.location = {assign: jest.fn()}

    const componentRequestData = new ComponentRequestData()
    mockAxios.onGet('/test-url').reply(200, {redirect: '/new-location'})

    await request(t, '/test-url', 'get', {}, {}, componentRequestData)

    expect(window.location.assign).toHaveBeenCalledWith('/new-location')
  })

  it('should handle attachments in response', async () => {
    global.URL.createObjectURL = jest.fn()

    const filename = 'file.txt'
    const data = 'File content'
    const createObjectURLSpy = jest.spyOn(window.URL, 'createObjectURL').mockReturnValue('mock-url')

    const createElementSpy = jest.spyOn(document, 'createElement').mockReturnValue({
      style: {},
      href: '',
      download: '',
      click: jest.fn(),
    })

    mockAxios.onGet('/test-url').reply(200, data, {
      'content-disposition': `attachment; filename=${filename}`,
    })

    await request(t, '/test-url')

    const anchorElement = createElementSpy.mock.results[0].value
    expect(createObjectURLSpy).toHaveBeenCalledWith(new Blob([data]))

    expect(createElementSpy).toHaveBeenCalledWith('a')
    expect(anchorElement.style.display).toBe('none')
    expect(anchorElement.href).toBe('mock-url')
    expect(anchorElement.download).toBe(filename)
    expect(createElementSpy).toHaveBeenCalledWith('a')
  })

  it('should handle errors in axios response', async () => {
    const componentRequestData = new ComponentRequestData()
    mockAxios.onGet('/test-url').reply(500, {message: 'Error'})

    await request(t, '/test-url', 'get', {}, {}, componentRequestData)

    expect(t.loading).toBe(false)
    expect(MoonShine.ui.toast).toHaveBeenCalledWith('Error', 'error')
  })

  it('should display "Unknown Error" if no error message is present', async () => {
    mockAxios.onGet('/test-url').networkError()

    await request(t, '/test-url')

    expect(MoonShine.ui.toast).toHaveBeenCalledWith('Unknown Error', 'error')
  })
})
