# ebanx-api-test
EBANX Software Engineer Take-home assignment

## Tecnologias Utilizadas

- PHP 8.x + Laravel 11  
- Laravel
- Redis (via Docker)

---

## Pré-requisitos

- PHP 8.x instalado  
- Composer  
- Docker (para rodar Redis)


Endpoints disponíveis

Método	Endpoint	Descrição	                                Exemplo JSON body
POST	/reset	    Reseta todas as contas	                    (sem body)
GET	    /balance	Consulta saldo da conta	                    ?account_id=1234
POST	/event	    Evento (deposit, withdraw, transfer)	    {"type":"deposit","destination":"100","amount":10}


michellynarita@hotmail.com
https://www.linkedin.com/in/michelly-narita-kuriyama-088158180/