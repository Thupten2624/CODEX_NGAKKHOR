(function () {
    const roleField = document.getElementById('role');
    const orgSelectWrap = document.getElementById('organization-select-wrap');
    const orgCreateWrap = document.getElementById('organization-create-wrap');

    if (!roleField || !orgSelectWrap || !orgCreateWrap) {
        return;
    }

    function toggleOrganizationFields() {
        const role = roleField.value;
        const isOrgAdmin = role === 'organization_admin';

        orgSelectWrap.style.display = isOrgAdmin ? 'none' : 'block';
        orgCreateWrap.style.display = isOrgAdmin ? 'block' : 'none';
    }

    roleField.addEventListener('change', toggleOrganizationFields);
    toggleOrganizationFields();
})();
