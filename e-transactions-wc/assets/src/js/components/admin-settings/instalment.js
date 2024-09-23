import {__} from '@wordpress/i18n';

const instalment = () => {

    const toggleEnabled     = document.querySelector('#WCE-JS-instalment-enabled');
    const tableTabs         = document.querySelector('#WCE-JS-instalment-table-tabs');
    const navItems          = document.querySelectorAll('.wc-etransactions-tabs__nav__item');
    const subpartInputs     = document.querySelectorAll('#WCE-JS-instalment-table-tabs input.subpart');

    if ( toggleEnabled ) {
        toggleEnabled.addEventListener( 'change', (e) => {
            if ( e.target.checked ) {
                jQuery( tableTabs ).slideDown();
            } else {
                jQuery( tableTabs ).hide();
            }
        });
    }

    if ( navItems ) {
        navItems.forEach( item  => {
            item.addEventListener( 'click', (e) => {

                if ( item.classList.contains('active') ) {
                    return;
                }

                const parent    = item.closest('.wc-etransactions-tabs');
                const Id        = item.dataset.id;

                if ( ! parent || ! Id ) {
                    return;
                }

                const contentItem = parent.querySelector(`.wc-etransactions-tabs__content__item[data-id="${Id}"]`);
                if ( ! contentItem ) {
                    return;
                }

                const otherContentItems = parent.querySelectorAll('.wc-etransactions-tabs__content__item');
                const otherNavItems     = parent.querySelectorAll('.wc-etransactions-tabs__nav__item');
                if ( otherContentItems ) {
                    otherContentItems.forEach( otherContentItem => {
                        otherContentItem.classList.remove('active');
                    });
                }
                if ( otherNavItems ) {
                    otherNavItems.forEach( otherNavItem => {
                        otherNavItem.classList.remove('active');
                    });
                }

                contentItem.classList.add('active');
                item.classList.add('active');
            });
        });
    }

    if ( subpartInputs ) {
        subpartInputs.forEach( input => {
            input.addEventListener( 'input', (e) => {

                const parentTr = input.closest('tr');
                if ( ! parentTr ) {
                    return;
                }

                const allSubpartInputs = parentTr.querySelectorAll('input.subpart');
                const subpartAuto      = parentTr.querySelector('.subpartAuto');

                if ( ! allSubpartInputs || ! subpartAuto ) {
                    return;
                }

                let sum = 0;
                allSubpartInputs.forEach( subpartInput => {
                    const value = parseInt( subpartInput.value, 10 ) || 0;
                    sum += value;
                });

                if ( sum > 99 ) {
                    input.value = 99 - ( sum - parseInt( input.value, 10 ) );
                    subpartAuto.value = 1;
                    return;
                }

                subpartAuto.value = 99 - sum + 1;
            });
        });
    }

};
document.addEventListener( 'DOMContentLoaded', () => instalment() );