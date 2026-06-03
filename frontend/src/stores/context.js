import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useContextStore = defineStore('context', () => {
  const university_id = ref('')
  const campus_id = ref('')
  const faculty_id = ref('')
  const department_id = ref('')

  // Init from localStorage
  const saved = localStorage.getItem('rdrims_context')
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      university_id.value = parsed.university_id || ''
      campus_id.value = parsed.campus_id || ''
      faculty_id.value = parsed.faculty_id || ''
      department_id.value = parsed.department_id || ''
    } catch (e) {
      console.error('Failed to parse context', e)
    }
  }

  // Watch and persist
  watch([university_id, campus_id, faculty_id, department_id], () => {
    localStorage.setItem('rdrims_context', JSON.stringify({
      university_id: university_id.value,
      campus_id: campus_id.value,
      faculty_id: faculty_id.value,
      department_id: department_id.value,
    }))
  }, { deep: true })

  function setUniversity(id) {
    university_id.value = id
    campus_id.value = ''
    faculty_id.value = ''
    department_id.value = ''
  }
  function setCampus(id) {
    campus_id.value = id
    faculty_id.value = ''
    department_id.value = ''
  }
  function setFaculty(id) {
    faculty_id.value = id
    department_id.value = ''
  }
  function setDepartment(id) {
    department_id.value = id
  }
  function resetContext() {
    university_id.value = ''
    campus_id.value = ''
    faculty_id.value = ''
    department_id.value = ''
  }

  return {
    university_id, campus_id, faculty_id, department_id,
    setUniversity, setCampus, setFaculty, setDepartment, resetContext
  }
})

