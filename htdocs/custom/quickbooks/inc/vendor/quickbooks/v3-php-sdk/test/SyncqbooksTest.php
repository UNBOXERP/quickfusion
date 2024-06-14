```php
<?php

use PHPUnit\Framework\TestCase;

class SyncqbooksTest extends TestCase
{
	private $syncqbooks;

	protected function setUp(): void
	{
		$this->syncqbooks = new Syncqbooks();
	}

	public function testUpdateFacturasQBooks()
	{
		$this->syncqbooks->expects($this->once())
			->method('getFacturasDolibarr')
			->willReturn(['factura1', 'factura2']);

		$this->syncqbooks->updateFacturasQBooks();
	}

	public function testGetFacturasQBooks()
	{
		$this->syncqbooks->expects($this->once())
			->method('getFacturasQbooksNoDolibarr')
			->willReturn(['factura1', 'factura2']);

		$this->syncqbooks->expects($this->once())
			->method('getFacturasSistema')
			->willReturn(['factura3', 'factura4']);

		$facturas = $this->syncqbooks->getFacturasQBooks(1);

		$this->assertCount(4, $facturas);
	}

	public function testUpdateFacturasDolibarr()
	{
		$this->syncqbooks->expects($this->once())
			->method('getFacturasQbooks')
			->willReturn(['factura1', 'factura2']);

		$this->syncqbooks->updateFacturasDolibarr();
	}

	public function testCreaFacturasQbooks()
	{
		$facturas = ['factura1', 'factura2'];
		$this->syncqbooks->expects($this->once())
			->method('CreaFacturasQbooks')
			->with($facturas);

		$this->syncqbooks->CreaFacturasQbooks($facturas);
	}

	public function testGetCustomer()
	{
		$factura = new stdClass();
		$factura->thirdparty = 'thirdparty1';

		$this->syncqbooks->expects($this->once())
			->method('GetCustomer')
			->with($factura->thirdparty)
			->willReturn(['customer1']);

		$customers = $this->syncqbooks->GetCustomer($factura);

		$this->assertCount(1, $customers);
	}

	public function testCreaClienteQbooks()
	{
		$thirdparty = 'thirdparty1';

		$this->syncqbooks->expects($this->once())
			->method('CreaClienteQbooks')
			->with($thirdparty)
			->willReturn(['customer1']);

		$customers = $this->syncqbooks->CreaClienteQbooks($thirdparty);

		$this->assertCount(1, $customers);
	}

	public function testGetProduct()
	{
		$fk_product = 'product1';

		$this->syncqbooks->expects($this->once())
			->method('GetProduct')
			->with($fk_product)
			->willReturn(['product1']);

		$products = $this->syncqbooks->GetProduct($fk_product);

		$this->assertCount(1, $products);
	}

	public function testCreaProductoQbooks()
	{
		$fk_product = 'product1';

		$this->syncqbooks->expects($this->once())
			->method('CreaProductoQbooks')
			->with($fk_product)
			->willReturn(['product1']);

		$products = $this->syncqbooks->CreaProductoQbooks($fk_product);

		$this->assertCount(1, $products);
	}
}
