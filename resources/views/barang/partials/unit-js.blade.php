<script>
    let rowIndex = document.querySelectorAll('#unitRows tr').length;
    function addUnitRow() {
      const html = document.querySelector('#rowTemplate')
                    .innerHTML.replace(/__INDEX__/g, rowIndex++);
      document.querySelector('#unitRows')
              .insertAdjacentHTML('beforeend', html);
    }
    function removeRow(btn) { btn.closest('tr').remove(); }
    window.addEventListener('DOMContentLoaded', () => {
      if (rowIndex === 0) addUnitRow();
    });
    </script>
