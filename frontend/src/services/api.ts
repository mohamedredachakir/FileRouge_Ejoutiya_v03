import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  if (config.data instanceof FormData) {
    // Let the browser set the proper multipart boundary.
    delete config.headers['Content-Type']
  } else if (!config.headers['Content-Type']) {
    config.headers['Content-Type'] = 'application/json'
  }

  const token = localStorage.getItem('ejoutiya_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    if (status === 401) {
      localStorage.removeItem('ejoutiya_token')
      window.dispatchEvent(new CustomEvent('ejoutiya:unauthorized'))
    } else if (status === 403) {
      window.dispatchEvent(new CustomEvent('ejoutiya:forbidden'))
    }
    return Promise.reject(error)
  }
)

export default api
