const config = () => {

    const openBtn        = document.querySelector('#JS-WCE-header-config-open');
    const containerTest  = document.querySelector('.wc-etransactions__header__config__popup__content__body__test-request');
    const downloadLogBtn = document.querySelector('#WCE-JS-config-donload-log');

    if ( openBtn ) {
        openBtn.addEventListener( 'click', (e) => {

            if ( containerTest.classList.contains('tested') ) {
                return;
            }

            let formData    = new FormData();
            formData.append( 'action', 'wc_etransactions_get_test_request' );
            formData.append( 'nonce', WC_ETRANSACTION_ADMIN_DATA.nonce );

            let requestOptions = {
                method: 'POST',
                body: formData,
                redirect: 'follow'
            };

            fetch(WC_ETRANSACTION_ADMIN_DATA.ajaxUrl, requestOptions)
            .then(response => response.json())
            .then(result => {
                if ( result.success ) {
                    containerTest.classList.add('success');
                } else {
                    containerTest.classList.add('fail');
                }
                containerTest.classList.add('tested');
            })
            .catch(error => {
                console.log( 'ERROR: ', error );
            });
        });
    }

    if ( downloadLogBtn ) {
        downloadLogBtn.addEventListener( 'click', (e) => {
            e.preventDefault();

            let formData    = new FormData();
            formData.append( 'action', 'wc_etransactions_get_log_file_content' );
            formData.append( 'nonce', WC_ETRANSACTION_ADMIN_DATA.nonce );

            let requestOptions = {
                method: 'POST',
                body: formData,
                redirect: 'follow'
            };

            fetch(WC_ETRANSACTION_ADMIN_DATA.ajaxUrl, requestOptions)
            .then(response => response.json())
            .then(result => {

                if ( result.success ) {

                    let fileName    = result.data.fileName;
                    let content     = result.data.content;

                    let data = new Blob( [content] );
                    let aElement = document.createElement('a');
                    aElement.setAttribute('download', fileName);
                    let href = URL.createObjectURL(data);
                    aElement.href = href;
                    aElement.setAttribute('target', '_blank');
                    aElement.click();
                    URL.revokeObjectURL(href);
    
                } else {
    
                    console.log( result.data );
                }

            })
            .catch(error => {
                console.log( 'ERROR: ', error );
            });
        });
    }


};
document.addEventListener( 'DOMContentLoaded', () => config() );