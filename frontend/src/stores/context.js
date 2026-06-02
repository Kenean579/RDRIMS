import { defineStore } from 'pinia'

export const useContextStore = defineStore('context', {
  state: () => ({
    university_id: '',
    campus_id: '',
    faculty_id: '',
    department_id: ''
  }),
  actions: {
    setUniversity(id) {
      this.university_id = id
      this.campus_id = ''
      this.faculty_id = ''
      this.department_id = ''
    },
    setCampus(id) {
      this.campus_id = id
      this.faculty_id = ''
      this.department_id = ''
    },
    setFaculty(id) {
      this.faculty_id = id
      this.department_id = ''
    },
    setDepartment(id) {
      this.department_id = id
    },
    resetContext() {
      this.university_id = ''
      this.campus_id = ''
      this.faculty_id = ''
      this.department_id = ''
    }
  }
})
