<?phpclass ControllerPaymentMoip extends Controller {	protected function index() {				$this->data['button_continue'] = $this->language->get('button_continue');		//Verifica se está em modo de teste		if (!$this->config->get('moip_test')) {        		$this->data['action']     = 'https://www.moip.com.br/ws/alpha/EnviarInstrucao/Unica';  		} else {			$this->data['action']     = 'https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica';		}				//Carrega parcelas		$this->data['parcelas_moip'] = $this->config->get('moip_parcelas');                		//Carrega o arquivo catalog/model/checkout/order.php		$this->load->model('checkout/order');				//Adiciona os dados da compra no array order_info		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);				//Captura a 'razão' cadastrato no módulo de pagamento MoiP no painel administrativo		$this->data['nometranzacao'] = $this->config->get('moip_razao');				//Captura o 'Token' cadastrato no módulo de pagamento MoiP no painel administrativo		$this->data['apitoken'] = $this->config->get('moip_apitoken');								//Captura a 'Key' cadastrato no módulo de pagamento MoiP no painel administrativo		$this->data['apikey'] = $this->config->get('moip_apikey');								//Captura o ID do Cliente		$this->data['customer_id'] = $order_info['customer_id'];				//Captura o tipo da moeda utilizada na compra		$this->data['currency_code'] = $order_info['currency_code'];				//Captura o valor total		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], FALSE);				//Captura o primeiro nome do Cliente e remove os caracteres especiais		$this->data['first_name'] = $this->removeAcentos($order_info['payment_firstname']);				//Captura o sobrenome do cliente e remove os caracteres especiais		$this->data['last_name'] = $this->removeAcentos($order_info['payment_lastname']);				//Captura o logadouro do cliente e remove os caracteres especiais		$this->data['address1'] = $this->removeAcentos($order_info['payment_address_1']);				//Captura o bairro do cliente e remove os caracteres especiais		$this->data['address2'] = $this->removeAcentos($order_info['payment_address_2']);				//Captura a cidade do Cliente e remove os caracteres especiais		$this->data['city'] = $this->removeAcentos($order_info['payment_city']);				//Captura o CEP do Cliente		$this->data['zip'] = $order_info['payment_postcode'];				//Captura o País do Cliente		$this->data['country'] = $order_info['payment_country'];				//Inicia a sessão com o id da compra		$this->session->data['order_id'];				//Captura o id da compra		$this->data['codipedido'] = $this->session->data['order_id'];				//Captura o email do Cliente		$this->data['email'] = $order_info['email'];				//Captura Dias de Expiração da configuração do boleto		$this->data['diasCorridosBoleto'] = $this->config->get('moip_diasCorridosBoleto');				//Captura a instrução 1 da configuração do boleto		$this->data['instrucaoUmBoleto'] = $this->config->get('moip_instrucaoUmBoleto');				//Captura a instrução 2 configuração do boleto		$this->data['instrucaoDoisBoleto'] = $this->config->get('moip_instrucaoDoisBoleto');				//Captura a instrução 3 da configuração do boleto		$this->data['instrucaoTresBoleto'] = $this->config->get('moip_instrucaoTresBoleto');				//Captura a url da logo da configuração do boleto		$this->data['urlLogoBoleto'] = $this->config->get('moip_urlLogoBoleto');				//Captura o modo de como o usuário irá visualizar as formas de pagamento		$this->data['modoParcela'] = ucfirst($this->config->get('moip_modoParcelas'));				//Acc Cartão de Crédito		$this->data['accCartaoCredito'] = ucfirst($this->config->get('moip_accCartaoCredito'));				//Acc Boleto		$this->data['accBoleto'] = ucfirst($this->config->get('moip_accBoleto'));				//Acc Débito		$this->data['accDebito'] = ucfirst($this->config->get('moip_accDebito'));				//Verifica se é para exibi o valor total das parcelas		if ($this->config->get('moip_exibiTotalParcela') == '1'):			$this->data['exibiTotalParcela'] =  "' = R$' + data.parcelas[i].valor_total + '</dt>'";		else:			$this->data['exibiTotalParcela'] =  "''";		endif;				//Invoice		$this->data['invoice'] = $this->session->data['order_id'] . ' - ' . $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];				/* Pega o id do país */				$this->load->model('localisation/country');                $paises = $this->model_localisation_country->getCountries();				foreach ($paises as $country) {			if($country['name']==$order_info['payment_country']){				$codigodopais = $country['country_id'];			}		}		/* Com id do país pega o code da cidade */		$this->load->model('localisation/zone');                $results = $this->model_localisation_zone->getZonesByCountryId($codigodopais);           		foreach ($results as $result) {                    if($result['name']==$order_info['payment_zone']){                                    $this->data['estado'] =$result['code'];                    }                } 		//Verifica se existe o ddd do cliente		if(isset($order_info['ddd'])){			$this->data['ddd'] = $order_info['ddd'];		} else {			$ntelefone = preg_replace("/[^0-9]/", "", $order_info['telephone']);			if(strlen($ntelefone) >= 10){					$ntelefone = ltrim($ntelefone, "0");				$this->data['ddd'] = substr($ntelefone, 0, 2);				$this->data['telephone'] = substr($ntelefone, 2,11);			} else {				$this->data['telephone'] = substr($ntelefone, 0,11);			}		}				//Adiciona a url que chama a função success na variavel $return		$this->data['return'] = HTTPS_SERVER . 'checkout/success';				//Captura o email cadastrado na página de pagamento MoiP no painel administrativo		$this->data['mailpg'] = $this->config->get('moip_email');				//Captura valor total e multiplica com o valor da moeda escolhida		if ($order_info['currency_code'] != 'BRL'):			$this->data['valorTotalPedido'] = $this->format_money(preg_replace('/[^0-9]/i','',$this->currency->format($order_info['total']*$order_info['currency_value'], $order_info['currency_value'], $this->currency->getValue('BRL'))));		else:			$this->data['valorTotalPedido'] = $order_info['total']*$order_info['currency_value'];		endif;				//var_dump($order_info);				//Link de redirecionamento		$this->data['continue'] = $this->url->link('checkout/success');				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/moip.tpl')) {			$this->template = $this->config->get('config_template') . '/template/payment/moip.tpl';		} else {			$this->template = 'default/template/payment/moip.tpl';		}					$this->render();						}		public function confirm() {		$this->load->language('payment/moip');		$this->load->model('checkout/order');			$comment  = $this->language->get('text_instruction') . "\n\n";		$comment .= $this->language->get('text_payment');		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'), $comment);				$html  .= '<strong>Pedido Número: </strong>' . $this->session->data['order_id'] . '<br/>';		$html  .= '<strong>Status Pagamento: </strong>' . $this->request->get['StatusPagamento'] . '<br/>';				if (!empty($this->request->get['Status']) && $this->request->get['Status'] != "undefined")			$html .= '<strong>Status: </strong>' . $this->request->get['Status'] . '<br/>';					if (!empty($this->request->get['CodigoMoIP']) && $this->request->get['CodigoMoIP'] != "undefined")			$html .= '<strong>Codigo MoIP: </strong>' . $this->request->get['CodigoMoIP'] . '<br/>';				if (!empty($this->request->get['TotalPago']) && $this->request->get['TotalPago'] != "undefined")			$html .= '<strong>Total Pago: </strong>R$' . $this->request->get['TotalPago'] . '<br/>';				if (!empty($this->request->get['TotalPago']) && $this->request->get['TaxaMoIP'] != "undefined")			$html .= '<strong>Taxa MoIP: </strong>R$' . $this->request->get['TaxaMoIP'] . '<br/>';				$html .= '<strong>Mensagem: </strong>' . $this->request->get['Mensagem'] . '<br/>';				if (!empty($this->request->get['CodigoRetorno']) && $this->request->get['CodigoRetorno'] != "undefined"):			$html .= '<strong>Codigo Retorno: </strong>' . $this->request->get['CodigoRetorno'] . '<br/>';		endif;				if ($this->request->get['Cod_Classificacao'] != 0):			$html .= '<strong>Codigo: </strong>' . $this->request->get['Cod_Classificacao'] . '<br/>';			$html .= '<strong>Descricao: </strong>' . $this->request->get['Descricao_Classificacao'];		endif;				$html .= '<br/><br/><small>Sistema desenvolvido por Valdeir S. &lt;valdeirpsr@hotmail.com&gt;</small>';				$mail = new Mail(); 		$mail->protocol = $this->config->get('config_mail_protocol');		$mail->parameter = $this->config->get('config_mail_parameter');		$mail->hostname = $this->config->get('config_smtp_host');		$mail->username = $this->config->get('config_smtp_username');		$mail->password = $this->config->get('config_smtp_password');		$mail->port = $this->config->get('config_smtp_port');		$mail->timeout = $this->config->get('config_smtp_timeout');					$mail->setTo($this->config->get('config_email'));		$mail->setFrom($this->config->get('config_email'));		$mail->setSender($this->config->get('config_name'));		$mail->setSubject('Pedido: #' . $this->session->data['order_id']);		$mail->setHtml($html);		$mail->send();				if (isset($this->session->data['order_id'])) {			$this->cart->clear();			unset($this->session->data['shipping_method']);			unset($this->session->data['shipping_methods']);			unset($this->session->data['payment_method']);			unset($this->session->data['payment_methods']);			unset($this->session->data['comment']);			unset($this->session->data['coupon']);		}			}		public function salvarCartao () {		//Carrega o model do MoiIP		$this->load->model('payment/moip');		//Carrega a livraria de criptografia		$this->load->library('criptografiacartao');		//Instacia um novo objeto de criptografia		$encryption = new CriptografiaCartao($this->config->get('config_encryption'));		//Captura o id do cliente		$dados['customer_id'] = $this->request->get['customer_id']; 		//Captura a bandeira do cartão		$dados['bandeiraCartao'] = $encryption->encrypt($this->request->get['bandeiraCartao']);		//Captura o nome do titular do cartão		$dados['titularCartao'] = $encryption->encrypt($this->request->get['titularCartao']); 		//Captura o número do cartão		$dados['numeroCartao'] = $encryption->encrypt($this->request->get['numeroCartao']); 		//Captura a data de validade do cartão		$dados['validadeCartao'] = $encryption->encrypt($this->request->get['validadeCartao']); 		//Captura o código de segurança do cartão		$dados['codCartao'] = $encryption->encrypt($this->request->get['codCartao']); 		//Captura a data de nascimento do titular		$dados['nascimentoTitular'] = $encryption->encrypt($this->request->get['nascimentoTitular']); 		//Captura o telefone do titular		$dados['telefone'] = $encryption->encrypt($this->request->get['telefone']); 		//Captura o cpf do titular		$dados['cpf'] = $encryption->encrypt($this->request->get['cpf']);		//Salva os dados do Cartão		$this->model_payment_moip->salvarCartao($dados);	}		public function getCartao () {		//Carrega o model do MoIP		$this->load->model('payment/moip');		//Captura os dados do cartão escolhido		$resultado = $this->model_payment_moip->getCartao($this->request->get['customer_id'],$this->request->get['bandeira']);		//Verifica se foi localizado		if (isset($resultado['localizado']) && $resultado['localizado'] === 'sim'):			echo json_encode($resultado);		else:			echo json_encode(array('error' => 'Nao Localizado'));		endif;	}		private function format_money($total){		if(strlen($total)>2){			$n=strlen($total)-2;			$preco=substr($total,0,$n).".".substr($total,$n);			return $preco;		}else{			return $total;		}	}		private function removeAcentos ($value) {		$acentos = array('Á','À','Â','Ã','É','Ê','Í','Ó','Ô','Õ','Ú','Ç','á','à','â','ã','é','ê','í','ó','ô','õ','ú','ç','æ');		$sAcentos = array('A','A','A','A','E','E','I','O','O','O','U','C','a','a','a','a','e','e','i','o','o','o','u','c','AE');				return str_replace($acentos, $sAcentos, $value);	}}?>