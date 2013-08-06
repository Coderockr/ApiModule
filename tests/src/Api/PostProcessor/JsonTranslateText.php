<?php
namespace Application\I18n;

use Core\Test\ServiceTestCase;
use Api\PostProcessor\Json;
use Zend\Http\Response;

/**
 * Testes de tradução
 * 
 * @category Application
 * @package I18n
 * @author  Mateus Guerra <mateus@coderockr.com>
 */

/**
 * @group I18n
 */
class JsonTranslateTest extends ServiceTestCase
{
    /**
     * Faz o setup dos testes
     * @return void
     */
    public function setup()
    {
        parent::setup();
        $this->buildTranslationFile();
    }

    /**
    * Teste do serviço de Translation
    * @return void
    */
    public function testArraySimples()
    {       
        $this->assertFalse(is_file('/tmp/pt_BR'));
        $content = array('texto' => 'name');
        $_SESSION['lang'] = 'pt_BR';
        $jsonProcessor = new Json(new Response, $content);
        $jsonProcessor->process();
        $response = $jsonProcessor->getResponse();
        $json = json_decode($response->getContent(), true);
        $this->assertEquals('nome',$json['texto']);
        $this->assertTrue(is_file('/tmp/pt_BR.php'));
    }

    /**
    * Teste do serviço de Translation
    * @return void
    */
    public function testArrayComplexo()
    {   
        $this->assertFalse(is_file('/tmp/pt_BR'));
        $content = array(
            'texto' => 'name',
            'interno' => array(
                'texto' => 'name',
                'interno2' => array(
                    'texto' => 'name'
                )
            )
        );
        $_SESSION['lang'] = 'pt_BR';
        $jsonProcessor = new Json(new Response, $content);
        $jsonProcessor->process();
        $response = $jsonProcessor->getResponse();
        $json = json_decode($response->getContent(), true);
        $this->assertEquals('nome',$json['texto']);
        $this->assertEquals('nome',$json['interno']['texto']);
        $this->assertEquals('nome',$json['interno']['interno2']['texto']);
        $this->assertTrue(is_file('/tmp/pt_BR.php'));
    }

    private function buildTranslationFile()
    {
        $start = '<?php return array(';
        $end = ');?>';
        $pt_BR = $start;
        $pt_BR .= "'name' => 'nome'";
        $pt_BR .= $end;
        
        file_put_contents('/tmp/pt_BR.php', $pt_BR);
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink('/tmp/pt_BR.php');
    }
}