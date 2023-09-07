import {
  getInputs,
  showWhenChange,
  showWhenVisibilityChange,
} from './showWhenFunctions'

export default () => ({
  whenFields: {},
  init(initData) {
    if (initData !== undefined && initData.whenFields !== undefined) {
      this.whenFields = initData.whenFields

      const inputs = this.getInputs()

      this.whenFields.forEach(field => {
        if (inputs[field.changeField] === undefined) {
          return
        }
        this.showWhenVisibilityChange(field.changeField, inputs, field)
      })
    }
  },
  precognition(form) {
    form.querySelector('.precognition_errors').innerHTML = ''

    submitState(form, true)

    axios
      .post(form.getAttribute('action'), new FormData(form), {
        headers: {
          Precognition: true,
          Accept: 'application/json',
          ContentType: form.getAttribute('enctype'),
        },
      })
      .then(function (response) {
        form.submit()
      })
      .catch(errorResponse => {
        submitState(form, false)

        let errors = ''
        let errorsData = errorResponse.response.data.errors
        for (const error in errorsData) {
          errors = errors + '<div class="mt-2 text-secondary">' + errorsData[error] + '</div>'
        }

        form.querySelector('.precognition_errors').innerHTML = errors
      })

    return false
  },

  async(form) {
    submitState(form, true)
    const t = this

    axios({
      url: form.getAttribute('action'),
      method: form.getAttribute('method'),
      data: new FormData(form),
      headers: {
        Accept: 'application/json',
        ContentType: form.getAttribute('enctype'),
      },
    })
    .then(function (response) {
      const data = response.data

      if(data.redirect) {
        window.location = data.redirect
      }

      t.$dispatch('toast', {type: 'success', text: data.message})

      submitState(form, false)
    })
    .catch(errorResponse => {
      const data = errorResponse.response.data

      t.$dispatch('toast', {type: 'error', text: data.message})

      submitState(form, false)
    })

    return false
  },

  onChangeField(event) {
    this.showWhenChange(event.target.getAttribute('name'))
  },

  showWhenChange,

  showWhenVisibilityChange,

  getInputs,
})

function submitState(form, loading = true) {
  if(!loading) {
    form.querySelector('.form_submit_button_loader').style.display = 'none'
    form.querySelector('.form_submit_button').removeAttribute('disabled')
  } else {
    form.querySelector('.form_submit_button').setAttribute('disabled', 'true')
    form.querySelector('.form_submit_button_loader').style.display = 'block'
  }
}
