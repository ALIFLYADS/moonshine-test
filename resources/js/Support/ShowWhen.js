export function getInputs(formId) {
  const inputs = {}
  document.querySelectorAll('#' + formId + ' [name]').forEach(element => {
    const value = element.getAttribute('name')
    const fieldName = inputFieldName(value)

    inputs[fieldName] = {}

    inputs[fieldName].value = inputGeValue(element)
    inputs[fieldName].type = element.getAttribute('type')
  })

  document.querySelectorAll('#' + formId + ' [data-show-field]').forEach(element => {
    const value = element.getAttribute('data-show-field')
    const fieldName = inputFieldName(value)

    inputs[fieldName] = {}

    inputs[fieldName].value = value
    inputs[fieldName].type = 'text'
  })

  document.querySelectorAll('#' + formId + ' [data-show-column]').forEach(element => {
    const fieldName = element.getAttribute('data-show-column')
    inputs[fieldName] = {}
    inputs[fieldName].value = inputGeValue(element)
    inputs[fieldName].type = element.getAttribute('type')
  })

  return inputs
}

export function showWhenChange(fieldName, formId) {
  fieldName = inputFieldName(fieldName)

  const showWhenConditions = []

  this.whenFields.forEach(field => {
    if (fieldName !== field.changeField) {
      return
    }

    let showField = field.showField

    if(! showWhenConditions[showField]) {
      showWhenConditions[showField] = []
    }

    showWhenConditions[showField].push(field)
  })

  for (let showField in showWhenConditions) {
    this.showWhenVisibilityChange(showWhenConditions[showField], showField, this.getInputs(formId), formId)
  }
}

export function showWhenVisibilityChange(showWhenConditions, fieldName, inputs, formId) {
  if (showWhenConditions.length === 0) {
    return
  }

  let inputElement = document.querySelector('#' + formId + ' [name="' + fieldName + '"]')

  if (inputElement === null) {
    inputElement = document.querySelector('#' + formId + ' [data-show-field="' + fieldName + '"]')
  }

  if (inputElement === null) {
    inputElement = document.querySelector('#' + formId + ' [data-show-column="' + fieldName + '"]')
  }

  if (inputElement === null) {
    return
  }

  let countTrueConditions = 0;
  showWhenConditions.forEach(field => {
    if (this.isValidateShow(fieldName, inputs, field)) {
      countTrueConditions++
    }
  })

  if(inputElement.closest('table')) {
    // If input is in a table, then find all tables with this input
    const tablesWithInput = []

    // Only data-show-field is used in tables, see in UI/Collections/Fields.php(prepareReindex)
    document.querySelectorAll('[data-show-field="' + fieldName + '"]').forEach(function (element) {
      let inputTable = element.closest('table')
        if(tablesWithInput.indexOf(inputTable) === -1) {
          tablesWithInput.push(inputTable)
        }
    })

    // Tables hide the entire column
    tablesWithInput.forEach(table => {
      showHideTableInputs(showWhenConditions.length === countTrueConditions, table, fieldName)
    })

    return;
  }

  // TODO in resources/views/components/fields-group.blade.php put a field in a container
  let fieldContainer = inputElement.closest('.moonshine-field')
  if (fieldContainer === null) {
    fieldContainer = inputElement.closest('.form-group')
  }
  if (fieldContainer === null) {
    fieldContainer = inputElement
  }

  if (showWhenConditions.length === countTrueConditions) {
    fieldContainer.style.removeProperty('display')

    const nameAttr = inputElement.getAttribute('data-show-column')
    if(nameAttr) {
      inputElement.setAttribute('name', nameAttr)
    }
  } else {
    fieldContainer.style.display = 'none'

    const nameAttr = inputElement.getAttribute('name');
    if(nameAttr) {
      inputElement.setAttribute('data-show-column', nameAttr);
      inputElement.removeAttribute('name')
    }
  }
}

function showHideTableInputs(isShow, table, fieldName) {

  let cellIndexTd = null;

  table.querySelectorAll('[data-show-field="' + fieldName + '"]').forEach(element => {
    if(isShow) {
      element.closest('td').style.removeProperty('display')
      const nameAttr = element.getAttribute('data-show-column')
      if(nameAttr) {
        element.setAttribute('name', nameAttr)
      }
    } else {
      element.closest('td').style.display = 'none'
      element.setAttribute('data-show-column', element.getAttribute('name'));
      element.removeAttribute('name')
    }

    if(cellIndexTd === null) {
      cellIndexTd = element.closest('td').cellIndex
    }
  })

  if(cellIndexTd !== null) {
    table.querySelectorAll('th').forEach((element) => {
      if(element.cellIndex !== cellIndexTd) {
        return
      }
      element.style.display = isShow ? 'block' : 'none'
    })
  }
}

export function isValidateShow(fieldName, inputs, field) {
  let validateShow = false

  let valueInput = inputs[field.changeField].value
  let valueField = field.value

  const inputType = inputs[field.changeField].type

  if (inputType === 'number') {
    valueInput = parseFloat(valueInput)
    valueField = parseFloat(valueField)
  } else if (inputType === 'date' || inputType === 'datetime-local') {
    if (inputType === 'date') {
      valueInput = valueInput + ' 00:00:00'
    }
    valueInput = new Date(valueInput).getTime()

    if (!Array.isArray(valueField)) {
      valueField = new Date(valueField).getTime()
    }
  }

  switch (field.operator) {
    case '=':
      validateShow = valueInput == valueField
      break
    case '!=':
      validateShow = valueInput != valueField
      break
    case '>':
      validateShow = valueInput > valueField
      break
    case '<':
      validateShow = valueInput < valueField
      break
    case '>=':
      validateShow = valueInput >= valueField
      break
    case '<=':
      validateShow = valueInput <= valueField
      break
    case 'in':
      if (Array.isArray(valueInput) && Array.isArray(valueField)) {
        for (let i = 0; i < valueField.length; i++) {
          if (valueInput.includes(valueField[i])) {
            validateShow = true
            break
          }
        }
      } else {
        validateShow = valueField.includes(valueInput)
      }
      break
    case 'not in':
      if (Array.isArray(valueInput) && Array.isArray(valueField)) {
        let includes = false
        for (let i = 0; i < valueField.length; i++) {
          if (valueInput.includes(valueField[i])) {
            includes = true
            break
          }
        }
        validateShow = !includes
      } else {
        validateShow = !valueField.includes(valueInput)
      }
      break
  }

  return validateShow
}

export function inputFieldName(inputName) {
  if (inputName === null) {
    return ''
  }
  inputName = inputName.replace('[]', '')
  if (inputName.indexOf('slide[') !== -1) {
    inputName = inputName.replace('slide[', '').replace(']', '')
  }
  return inputName
}

export function inputGeValue(element) {
  let value

  const type = element.getAttribute('type')

  if (element.hasAttribute('multiple') && element.options !== undefined) {
    value = []
    for (let option of element.options) {
      if (option.selected) {
        value.push(option.value)
      }
    }
  } else if (type === 'checkbox' || type === 'radio') {
    value = element.checked
  } else {
    value = element.value
  }

  return value
}
