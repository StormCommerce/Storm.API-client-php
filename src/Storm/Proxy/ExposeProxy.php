<?php


namespace Storm\Proxy;

use Storm\Expose\ExposeGenerator;
use Storm\Expose\ExposePhp;
use Storm\Expose\ExposeService;
use Storm\Expose\Wsdl;
use Storm\StormClient;
use Storm\Util\Str;
use Wsdl2PhpGenerator\Config;

class ExposeProxy extends AbstractProxy
{
    public function generateClasses()
    {
        if (!is_dir($this->path())) {
            mkdir($this->path());
        }
        $serviceAnnotations = "<?php" . PHP_EOL;
        $proxyRepository = StormClient::self()->proxies();
        foreach ($proxyRepository->services() as $proxy => $properties) {

            $wsdl = new Wsdl($this->get($properties['service'] . "?singleWsdl"), "{$properties['service']}.wsdl", $this->path());
            $wsdl->save();

            $generator = new ExposeGenerator();
            $generator->generate(new Config(
                [
                    'inputFile' => $wsdl->path(),
                    'outputDir' => $this->path(),
                ]
            ));

            $operations = json_decode($this->get("docs/service/ajax.svc/getcontract/{$properties['mappingKey']}"), true);
            $entities = json_decode($this->get("docs/service/ajax.svc/listentities/{$properties['mappingKey']}"), true);
            $collections = json_decode($this->get("docs/service/ajax.svc/listcollections/{$properties['mappingKey']}"), true);
            if ($entities !== null) {
                $proxyRepository->entities($properties['mappingKey'])->build($entities)->save();
            }
            if ($collections !== null) {
                $proxyRepository->collections($properties['mappingKey'])->build($collections)->save();
            }
            if ($operations !== null) {
                $proxy = new ExposeService($operations, $properties['mappingKey']);
                $proxyRepository->operations($properties['mappingKey'])->build($operations)->save();
                $serviceAnnotations .= $proxy->buildAnnotations();
            }
        }
        ExposePhp::instance()->save();
        file_put_contents($this->path() . "storm-services.php", $serviceAnnotations);
    }

    public function path()
    {
        return StormClient::self()->exposePath();
    }

    protected function uri($path = "")
    {
        return $this->accessClient()->baseUrl() . $path;
    }

}