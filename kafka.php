<?php
require_once __DIR__.'/Thrift/ClassLoader/ThriftClassLoader.php';
require_once __DIR__.'/com/feiniu/kafka/thrift/service/KafkaService.php';
require_once __DIR__.'/com/feiniu/kafka/thrift/service/Types.php';
require_once __DIR__.'/config.php';
require_once __DIR__.'/comm_fun.php';

use Thrift\ClassLoader\ThriftClassLoader;



use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;
use \com\feiniu\kafka\thrift\service\KeyedMessage as kafkaMsg;
class kafka{
    protected static $instance=null;
    
    private function __construct(){
        $GEN_DIR = realpath(dirname(__FILE__).'/..').'/gen-php';
        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', __DIR__ );
        $loader->registerDefinition('shared', $GEN_DIR);
        $loader->registerDefinition('tutorial', $GEN_DIR);
        $loader->register();

        $kafkaUrl =  getconfig('kafkaUrl');
        $port =  getconfig('kafkaPort');
        
        $socket = new TSocket($kafkaUrl, $port);
        $transport = new TBufferedTransport($socket, 1024, 1024);
        $transport->open();
        $protocol = new TBinaryProtocol($transport);
        $this->client = new \com\feiniu\kafka\thrift\service\KafkaService($protocol);
        
        $this->project = getconfig('kafkaProject');
        $this->topic = getconfig('kafkaTopic');
        $this->kafkakey = getconfig('kafkaKey');
        $this->group = getconfig('kafkaGroup');
        //$this->msg = getconfig('');
    }
    
    public static function getInstance(){
        if(is_null(self::$instance)){  
            self::$instance = new kafka;  
        }  
        return self::$instance;  
    }
    
    public function sendList($storeId,$list){
        $data = [];
        $obj = $this->client->hasProducer($this->project,$storeId);
        $arr = object2Array($obj);
        if($arr['status'] != 0){
            $this->client->createProducer($this->project,$storeId);
        }
        foreach($list as $li){
   $data[] = new \com\feiniu\kafka\thrift\service\KeyedMessage(array('topic' =>$storeId,'key' => $this->kafkakey, 'message'=> json_encode($li)));
        }
        $this->client->sendList($data);
    }
    
    public function get($storeId){
        $obj = $this->client->hasConsumer($this->project, '1', $this->group, $storeId);
        $arr = object2Array($obj);
        if($arr['status'] != 0) {
            $this->client->createConsumer($this->project, '1', $this->group, $storeId);
        }
        return $this->client->batchConsume($this->group, $storeId, 5);
    }

}
