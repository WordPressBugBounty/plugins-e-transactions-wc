const paymentInfo = () => {

	const submitBtns = document.querySelectorAll( 'button[name="wc-etransactions-order-submit"]' );
	const refundBtns = document.querySelectorAll( 'button[name="wc-etransactions-order-refund"]' );

	submitBtns.forEach( btn => {
		btn.addEventListener( 'click', (e) => {
			e.preventDefault();

			let message = btn.nextElementSibling;
			
			message.innerHTML = '';
			btn.classList.add( 'loading' );

			const form     = btn.closest( 'form' );
			const formData = new FormData( form );
			const object   = {};

			formData.forEach( ( value, key ) => {
				object[key] = value;
			});

			const json = JSON.stringify( object );

			const requestData = new FormData();
            requestData.append( 'action', 'wc_etransactions_admin_single_order_submit' );
            requestData.append( 'nonce', wc_etransactions_admin_single_order.nonce );
			requestData.append( 'form', json );

			const requestOptions = {
                method: 'POST',
                body: requestData,
                redirect: 'follow'
            };

			fetch( wc_etransactions_admin_single_order.ajaxUrl, requestOptions )
            .then( response => response.json() )
            .then( response => {

				if ( response.success ) {
					window.location.reload();
				} else {
					message.innerHTML = response.data;
					btn.classList.remove( 'loading' );
				}
			})
			.catch( error => {
                console.log( 'Error...' );
            });
		});
	});

	refundBtns.forEach( btn => {
		btn.addEventListener( 'click', (e) => {
			e.preventDefault();

			let message = btn.nextElementSibling;

			message.innerHTML = '';
			btn.classList.add( 'loading' );

			const form     = btn.closest( 'form' );
			const formData = new FormData( form );
			const object   = {};

			formData.forEach( ( value, key ) => {
				object[key] = value;
			});

			const json = JSON.stringify( object );

			const requestData = new FormData();
            requestData.append( 'action', 'wc_etransactions_admin_single_order_refund' );
            requestData.append( 'nonce', wc_etransactions_admin_single_order.nonce );
			requestData.append( 'form', json );

            const requestOptions = {
                method: 'POST',
                body: requestData,
                redirect: 'follow'
            };

			fetch( wc_etransactions_admin_single_order.ajaxUrl, requestOptions )
            .then( response => response.json() )
            .then( response => {

				if ( response.success ) {
					window.location.reload();
				} else {
					message.innerHTML = response.data;
					btn.classList.remove( 'loading' );
				}
			})
			.catch( error => {
                console.log( 'Error...' );
            });
		});
	});

};

document.addEventListener( 'DOMContentLoaded', () => { paymentInfo(); } );