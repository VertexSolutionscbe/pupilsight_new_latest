In this sample project, the following are sample php files for Transaction Payment Process at the time checkout in the Merchant site:
	
	1.meTrnCardReq.html		: This page captures the transaction amount,card details and other details.
	2.meTrnCardPay.php		: This page generates the transaction request message based on transaction parameter passed from above page and redirects the customer to PG Pay Page.
	3.meTrnSuccess.php		: Post transaction processing, PG redirects the customer to this page where encryption key has to be entered for response message decryption
							  and parse the transaction response message and displays the transaction status
	
	
The following are sample files for Transaction Status API:
	
	1.meTrnStatusReq.html	: This page captures the transaction reference or order id to fetch the transaction status.
	2.meTrnStausSuccess.php	: This retrieves the transaction status based on the order id and displays it.
	

	
The following are sample files for Cancel Transaction Status API:
	
	1.meTrnCardReq.html	         : This page captures the transaction and order id details which required to cancel the transaction
	2.meTrnCardStatusSuccess.php : This page calls the cancel API, retrieve the status and displays it.
	
	
	
The following are sample files for Refund Transaction Status API:
	
	1.meTrnRefundReq.html	: This page captures the transaction and order id details which required to refunded the transaction
	2.meTrnRefundSuccess.php: This page calls the refund API, retrieve the status and displays it.
	