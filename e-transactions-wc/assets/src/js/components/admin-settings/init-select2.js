import select2 from 'select2';

const initSelect2 = () => {

    jQuery('.wce-select2').select2({
        placeholder: WC_ETRANSACTION_ADMIN_DATA.i18n.select,
        language: {
            noResults: function(){
                return WC_ETRANSACTION_ADMIN_DATA.i18n.noResults;
            }
        }
    });

};
document.addEventListener( 'DOMContentLoaded', () => initSelect2() );