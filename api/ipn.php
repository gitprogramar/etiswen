<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// IPN (Instant Payment Notification)
	
	/*
	//Usage:
		/api/ipn.php?topic=payment&id=123456789
	*/
	
	define('_JEXEC', 1);
	require_once ('utils.php');
	$utils = new Utils();
	
	$topic = $_GET['topic'];
	$id = $_GET['id'];
	if(!isset($topic) || !isset($id)) {		
		echo 'No data set.';
		return;
	}
	else if($topic != 'payment'){
		echo 'topic must be payment.';	
		return;			
	}

	$pay = $utils->mercadopago()->get_payment_info($id)["response"]["collection"];

	$payment = new Payment();
	$payment->id = $pay["id"];
	$payment->reason = $pay["reason"];
	$payment->email = $pay["payer"]["email"];
	$payment->name = $pay["payer"]["first_name"]. ' '.$pay["payer"]["last_name"];
	$payment->phone = $pay["payer"]["phone"]["area_code"].' '.$pay["payer"]["phone"]["number"];	

	$html = '<h2>Detalle de la compra:</h2>';
	$html .= '<p><span>Operación:</span> ';
	$html .= '<strong>'.$payment->id.'</strong></p>';
    $html .= '<p><span>Item: </span> ';
	$html .= '<strong>'.$payment->reason.'</strong></p>';
	$html .= '<p><span>Nombre: </span> ';
	$html .= '<strong>'.$payment->name.'</strong></p>';
	$html .= '<p><span>Correo: </span> ';
	$html .= '<strong>'.$payment->email.'</strong></p>';	
	$html .= '<p><span>Teléfono: </span> ';
	$html .= '<strong>'.$payment->phone.'</strong></p>';
	$html .= '<br><br><p>Contacta al comprador y coordiná los detalles de tu venta</p>';
	$html .= '<br><h3>'.JFactory::getConfig()->get('sitename').'</h3>';
	
	if(strpos($payment->reason, 'Carro de compras') !== false) {
		$utils->sendMail($html, "¡Compraron en tu sitio!");
		echo 'Correo enviado.';
	}
	else {
		echo 'Not a site payment.';	
	}
	
	class Payment {
		public $id = "";
		public $reason = "";
		public $email = "";
		public $name = "";
		public $phone = "";
	}	
?>
<script>
window.addEventListener("load", function e(t) {
    window.removeEventListener("load", e, !1);
	/*program.post("/index.php?option=com_rokquickcart&task=ipn&format=raw", {topic: "<?=$topic?>", id:"<?=$id?>"}, function(response) {
		console.log(response);
	});*/
});
</script>