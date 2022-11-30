<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster\Caster;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Exception\ThrowingCasterException;
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = ['__PHP_Incomplete_Class' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\Caster', 'castPhpIncompleteClass'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\CutStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\CutArrayStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castCutArray'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ConstStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\EnumStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castEnum'], 'Fiber' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\FiberCaster', 'castFiber'], 'Closure' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClosure'], 'Generator' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castGenerator'], 'ReflectionType' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castType'], 'ReflectionAttribute' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castAttribute'], 'ReflectionGenerator' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReflectionGenerator'], 'ReflectionClass' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClass'], 'ReflectionClassConstant' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClassConstant'], 'ReflectionFunctionAbstract' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castFunctionAbstract'], 'ReflectionMethod' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castMethod'], 'ReflectionParameter' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castParameter'], 'ReflectionProperty' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castProperty'], 'ReflectionReference' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReference'], 'ReflectionExtension' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castExtension'], 'ReflectionZendExtension' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castZendExtension'], '_HumbugBoxb47773b41c19\\Doctrine\\Common\\Persistence\\ObjectManager' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Doctrine\\Common\\Proxy\\Proxy' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castCommonProxy'], '_HumbugBoxb47773b41c19\\Doctrine\\ORM\\Proxy\\Proxy' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castOrmProxy'], '_HumbugBoxb47773b41c19\\Doctrine\\ORM\\PersistentCollection' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castPersistentCollection'], '_HumbugBoxb47773b41c19\\Doctrine\\Persistence\\ObjectManager' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'DOMException' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castException'], 'DOMStringList' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNameList' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMImplementation' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castImplementation'], 'DOMImplementationList' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNode' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNode'], 'DOMNameSpaceNode' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNameSpaceNode'], 'DOMDocument' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocument'], 'DOMNodeList' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNamedNodeMap' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMCharacterData' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castCharacterData'], 'DOMAttr' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castAttr'], 'DOMElement' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castElement'], 'DOMText' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castText'], 'DOMDocumentType' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocumentType'], 'DOMNotation' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNotation'], 'DOMEntity' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castEntity'], 'DOMProcessingInstruction' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castProcessingInstruction'], 'DOMXPath' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castXPath'], 'XMLReader' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\XmlReaderCaster', 'castXmlReader'], 'ErrorException' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castErrorException'], 'Exception' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castException'], 'Error' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castError'], '_HumbugBoxb47773b41c19\\Symfony\\Bridge\\Monolog\\Logger' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\AmpHttpClient' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\CurlHttpClient' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\NativeHttpClient' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\Response\\AmpResponse' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\Response\\CurlResponse' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpClient\\Response\\NativeResponse' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\HttpFoundation\\Request' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castRequest'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\Uid\\Ulid' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castUlid'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\Uid\\Uuid' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castUuid'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Exception\\ThrowingCasterException' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castThrowingCasterException'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\TraceStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castTraceStub'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\FrameStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castFrameStub'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Cloner\\AbstractCloner' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\ErrorHandler\\Exception\\SilencedErrorContext' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castSilencedErrorContext'], '_HumbugBoxb47773b41c19\\Imagine\\Image\\ImageInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ImagineCaster', 'castImage'], '_HumbugBoxb47773b41c19\\Ramsey\\Uuid\\UuidInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\UuidCaster', 'castRamseyUuid'], '_HumbugBoxb47773b41c19\\ProxyManager\\Proxy\\ProxyInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ProxyManagerCaster', 'castProxy'], 'PHPUnit_Framework_MockObject_MockObject' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\PHPUnit\\Framework\\MockObject\\MockObject' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\PHPUnit\\Framework\\MockObject\\Stub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Prophecy\\Prophecy\\ProphecySubjectInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], '_HumbugBoxb47773b41c19\\Mockery\\MockInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'PDO' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdo'], 'PDOStatement' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdoStatement'], 'AMQPConnection' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castConnection'], 'AMQPChannel' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castChannel'], 'AMQPQueue' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castQueue'], 'AMQPExchange' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castExchange'], 'AMQPEnvelope' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castEnvelope'], 'ArrayObject' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayObject'], 'ArrayIterator' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayIterator'], 'SplDoublyLinkedList' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castDoublyLinkedList'], 'SplFileInfo' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileInfo'], 'SplFileObject' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileObject'], 'SplHeap' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'SplObjectStorage' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castObjectStorage'], 'SplPriorityQueue' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'OuterIterator' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castOuterIterator'], 'WeakReference' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castWeakReference'], 'Redis' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedis'], 'RedisArray' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisArray'], 'RedisCluster' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisCluster'], 'DateTimeInterface' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castDateTime'], 'DateInterval' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castInterval'], 'DateTimeZone' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castTimeZone'], 'DatePeriod' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castPeriod'], 'GMP' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\GmpCaster', 'castGmp'], 'MessageFormatter' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castMessageFormatter'], 'NumberFormatter' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castNumberFormatter'], 'IntlTimeZone' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlTimeZone'], 'IntlCalendar' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlCalendar'], 'IntlDateFormatter' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlDateFormatter'], 'Memcached' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\MemcachedCaster', 'castMemcached'], '_HumbugBoxb47773b41c19\\Ds\\Collection' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castCollection'], '_HumbugBoxb47773b41c19\\Ds\\Map' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castMap'], '_HumbugBoxb47773b41c19\\Ds\\Pair' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPair'], '_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DsPairStub' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPairStub'], 'mysqli_driver' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\MysqliCaster', 'castMysqliDriver'], 'CurlHandle' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castCurl'], ':dba' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], ':dba persistent' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], 'GdImage' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':gd' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':pgsql large object' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLargeObject'], ':pgsql link' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql link persistent' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql result' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castResult'], ':process' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castProcess'], ':stream' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], 'OpenSSLCertificate' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':OpenSSL X.509' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':persistent stream' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], ':stream-context' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStreamContext'], 'XmlParser' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], ':xml' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], 'RdKafka' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castRdKafka'], '_HumbugBoxb47773b41c19\\RdKafka\\Conf' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castConf'], '_HumbugBoxb47773b41c19\\RdKafka\\KafkaConsumer' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castKafkaConsumer'], '_HumbugBoxb47773b41c19\\RdKafka\\Metadata\\Broker' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castBrokerMetadata'], '_HumbugBoxb47773b41c19\\RdKafka\\Metadata\\Collection' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castCollectionMetadata'], '_HumbugBoxb47773b41c19\\RdKafka\\Metadata\\Partition' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castPartitionMetadata'], '_HumbugBoxb47773b41c19\\RdKafka\\Metadata\\Topic' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicMetadata'], '_HumbugBoxb47773b41c19\\RdKafka\\Message' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castMessage'], '_HumbugBoxb47773b41c19\\RdKafka\\Topic' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopic'], '_HumbugBoxb47773b41c19\\RdKafka\\TopicPartition' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicPartition'], '_HumbugBoxb47773b41c19\\RdKafka\\TopicConf' => ['_HumbugBoxb47773b41c19\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicConf']];
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;
    private array $casters = [];
    private $prevErrorHandler;
    private array $classInfo = [];
    private int $filter = 0;
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;
    }
    public function setMaxString(int $maxString)
    {
        $this->maxString = $maxString;
    }
    public function setMinDepth(int $minDepth)
    {
        $this->minDepth = $minDepth;
    }
    public function cloneVar(mixed $var, int $filter = 0) : Data
    {
        $this->prevErrorHandler = \set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }
            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }
            return \false;
        });
        $this->filter = $filter;
        if ($gc = \gc_enabled()) {
            \gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                \gc_enable();
            }
            \restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }
    protected abstract function doClone(mixed $var) : array;
    protected function castObject(Stub $stub, bool $isNested) : array
    {
        $obj = $stub->value;
        $class = $stub->class;
        if (\str_contains($class, "@anonymous\x00")) {
            $stub->class = \get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = \method_exists($class, '__debugInfo');
            foreach (\class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (\class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';
            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(Stub::class) ? [] : ['file' => $r->getFileName(), 'line' => $r->getStartLine()];
            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }
        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);
        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '') . '⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
    protected function castResource(Stub $stub, bool $isNested) : array
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;
        try {
            if (!empty($this->casters[':' . $type])) {
                foreach ($this->casters[':' . $type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '') . '⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
}
