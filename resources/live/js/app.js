let http = axios
// default
http.defaults.baseURL = 'http://127.0.0.1:9090/'
http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded'
http.defaults.timeout = 5000

// 添加响应拦截器
axios.interceptors.response.use(
    response => {
        if (response.status === 200) {
            return response.data
        }
        return response
    },
    error => {
        // 请求超时
        if (
            error.code === 'ECONNABORTED' &&
            error.message.indexOf('timeout') !== -1
        ) {
            alert('请求超时')
        }
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    alert(error.response.data.message)
                    break
                case 404:
                    alert('请求的接口不存在')
                    break
                case 422:
                    let errors = error.response.data.errors
                    alert(errors[0])
                    break
                case 500:
                    alert('服务器内部错误')
                    break
                default:
                    alert('服务器开了小差')
                    break
            }
        }
        return Promise.reject(error)
    }
)

Vue.prototype.$http = http
