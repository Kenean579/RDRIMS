import re
import os

def fix_file(filepath, patterns):
    if not os.path.exists(filepath): return
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    modified = False
    for old_str, new_str in patterns:
        old_crlf = old_str.replace('\n', '\r\n')
        new_crlf = new_str.replace('\n', '\r\n')
        if old_str in content:
            content = content.replace(old_str, new_str)
            modified = True
        elif old_crlf in content:
            content = content.replace(old_crlf, new_crlf)
            modified = True
            
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed {os.path.basename(filepath)}")

# PublicEventsView
events_patterns = [(
"""    const [u, c, f, d] = await Promise.all([
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments')
    ])""",
"""    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')"""
)]
fix_file(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\PublicEventsView.vue', events_patterns)

# PublicPublicationsView
pubs_patterns = [(
"""    const [u, c, f, d, pubs] = await Promise.all([
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments'),
      api.get('/publications', { params: ...fetchParams.value })
    ])""",
"""    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')
    const pubs = await api.get('/publications', { params: ...fetchParams.value })"""
)]
fix_file(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\PublicPublicationsView.vue', pubs_patterns)

# PublicCallsView
calls_patterns = [(
"""    const [cs, u, c, f, d] = await Promise.all([
      api.get('/lookups/call_statuses'),
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments')
    ])""",
"""    const cs = await api.get('/lookups/call_statuses')
    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')"""
)]
fix_file(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\PublicCallsView.vue', calls_patterns)

# PublicResearchersView
researchers_patterns = [(
"""    const [u, c, f, d] = await Promise.all([
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments')
    ])""",
"""    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')"""
)]
fix_file(r'd:\proje\qelemeda\RDRIMS\frontend\src\views\public\PublicResearchersView.vue', researchers_patterns)

print("Done")
