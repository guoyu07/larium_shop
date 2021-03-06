<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Larium\Shop\Payment;

use Larium\FixtureHelper;
use Larium\Shop\Sale\Order;
use Finite\Loader\ArrayLoader;
use Finite\StateMachine\StateMachine;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    use FixtureHelper;

    public function testCreateInstance()
    {
        $method = $this->paymentMethods('creditcard_payment_method');
        $payment = new Payment(new Order(), $method);

        $this->assertInstanceOf('Larium\\Shop\\Payment\\PaymentInterface', $payment);

        $this->assertNotNull($payment->getIdentifier());

        $this->assertInstanceOf('Larium\\Shop\\Common\\Collection', $payment->getTransactions());
    }

    public function testCreditCardPayment()
    {
        $method = $this->paymentMethods('creditcard_payment_method');
        $method->setSourceOptions($this->getValidCreditCardOptions());
        $payment = new Payment(new Order(), $method, 100);
        $sm = $this->initializeStateMachine($payment);
        $sm->apply('purchase');

        $this->assertEquals('paid', $payment->getState());
    }

    public function testCashOnDeliveryPayment()
    {
        $method = $this->paymentMethods('cash_on_delivery_payment_method');

        $payment = new Payment(new Order(), $method, 100);
        $sm = $this->initializeStateMachine($payment);
        $sm->apply('purchase');

        $this->assertEquals('paid', $payment->getState());
        $this->assertEquals(100, $payment->getAmount());
    }

    private function initializeStateMachine($payment)
    {
        $reflection = new PaymentStateReflection();

        $config = $reflection->getStateConfig();

        $loader = new ArrayLoader($config);

        $state_machine = new StateMachine($payment);

        $loader->load($state_machine);

        $state_machine->initialize();

        return $state_machine;
    }
}
