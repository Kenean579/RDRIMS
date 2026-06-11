import re

with open(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\HomeView.vue', 'r', encoding='utf-8') as f:
    content = f.read()

old_content = """    const [uniRes, callsRes, pubRes, commRes] = await Promise.all([
      api.get('/universities'),
      api.get('/calls', { params: { status: 'open', per_page: 1 } }),
      api.get('/publications', { params: { per_page: 1 } }),
      api.get('/community-problems', { params: { status: 'completed', per_page: 1 } })
    ])"""

new_content = """    const uniRes = await api.get('/universities')
    const callsRes = await api.get('/calls', { params: { status: 'open', per_page: 1 } })
    const pubRes = await api.get('/publications', { params: { per_page: 1 } })
    const commRes = await api.get('/community-problems', { params: { status: 'completed', per_page: 1 } })"""

old_crlf = old_content.replace('\n', '\r\n')
new_crlf = new_content.replace('\n', '\r\n')

if old_content in content:
    content = content.replace(old_content, new_content)
    print("Replaced LF")
elif old_crlf in content:
    content = content.replace(old_crlf, new_crlf)
    print("Replaced CRLF")
else:
    print("Pattern not found!")

with open(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\HomeView.vue', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")
