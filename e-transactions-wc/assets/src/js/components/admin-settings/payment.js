const payment = () => {

    const radioDisplaySimple        = document.querySelector('#WCE-JS-payment-display-simple');
    const radioDisplayDetailed      = document.querySelector('#WCE-JS-payment-display-detailed');
    const tableDisplaySimple        = document.querySelector('#WCE-JS-payment-table-simple');
    const tableDisplayDetailed      = document.querySelector('#WCE-JS-payment-table-detailed');
    const radioDebitTypeImmediate   = document.querySelector('#WCE-JS-payment-debit-type-immediate');
    const radioDebitTypeDeferred    = document.querySelector('#WCE-JS-payment-debit-type-deferred');
    const tableDebitTypeDeferred    = document.querySelector('#WCE-JS-payment-table-deferred');
    const radioCaptureEventDays     = document.querySelector('#WCE-JS-payment-capture-event-days');
    const radioCaptureEventStatus   = document.querySelector('#WCE-JS-payment-capture-event-status');
    const tableCaptureEventDays     = document.querySelector('#WCE-JS-payment-table-days');
    const tableCaptureEventStatus   = document.querySelector('#WCE-JS-payment-table-status');
    const selectAddMeans            = document.querySelector('#WCE-JS-payment-select-add-means');
    const removeContractBtns        = document.querySelectorAll('.WCE-JS-remove-contract');

    if ( radioDisplaySimple ) {
        radioDisplaySimple.addEventListener( 'change', () => {
            jQuery( tableDisplaySimple ).slideDown();
            jQuery( tableDisplayDetailed ).hide();
        });
    }

    if ( radioDisplayDetailed ) {
        radioDisplayDetailed.addEventListener( 'change', () => {
            jQuery( tableDisplaySimple ).hide();
            jQuery( tableDisplayDetailed ).slideDown();
        });
    }

    if ( radioDebitTypeImmediate ) {
        radioDebitTypeImmediate.addEventListener( 'change', (e) => {
            if ( e.target.checked ) {
                jQuery( tableDebitTypeDeferred ).hide();
            }
        });
    }

    if ( radioDebitTypeDeferred ) {
        radioDebitTypeDeferred.addEventListener( 'change', (e) => {
            if ( e.target.checked ) {
                jQuery( tableDebitTypeDeferred ).slideDown();
            }
        });
    }

    if ( radioCaptureEventDays ) {
        radioCaptureEventDays.addEventListener( 'change', (e) => {
            if ( e.target.checked ) {
                jQuery( tableCaptureEventDays ).slideDown();
                jQuery( tableCaptureEventStatus ).hide();
            }
        });
    }

    if ( radioCaptureEventStatus ) {
        radioCaptureEventStatus.addEventListener( 'change', (e) => {
            if ( e.target.checked ) {
                jQuery( tableCaptureEventDays ).hide();
                jQuery( tableCaptureEventStatus ).slideDown();
            }
        });
    }

    if ( selectAddMeans ) {
        selectAddMeans.addEventListener( 'change', (e) => {
            
            const parentTable   = selectAddMeans.closest('table');
            if ( ! parentTable ) {
                return;
            }

            const value     = e.target.value;
            const desiredTr = parentTable.querySelector(`tr[data-id="${value}"]`);
            const option    = e.target.querySelector(`option[value="${value}"]`);
            if ( ! desiredTr || ! option ) {
                return;
            }

            const inputIsSelectable = desiredTr.querySelector('.input-isSelectable');
            if ( ! inputIsSelectable ) {
                return;
            }

            desiredTr.classList.remove('selectable');
            inputIsSelectable.value = '0';
            option.style.display    = 'none';
            e.target.value          = '-1';
        });
    }

    if ( removeContractBtns ) {
        removeContractBtns.forEach( btn => {
            btn.addEventListener( 'click', (e) => {

                const parentTr = e.target.closest('tr');
                if ( ! parentTr ) {
                    return;
                }

                const value             = btn.dataset.id;
                const option            = selectAddMeans.querySelector(`option[value="${value}"]`);
                const inputIsSelectable = parentTr.querySelector('.input-isSelectable');
                if ( ! option || ! inputIsSelectable ) {
                    return;
                }

                inputIsSelectable.value = '1';
                parentTr.classList.add('selectable');
                option.style.display    = 'block';
            });
        });
    }
};
document.addEventListener( 'DOMContentLoaded', () => payment() );