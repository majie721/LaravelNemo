<?php


namespace LaravelNemo;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Traits\PropertyArrayAccessTrait;
use LaravelNemo\Traits\ValidateTrait;

class Nemo implements \ArrayAccess
{

    use PropertyArrayAccessTrait,ValidateTrait;

    /** @var bool  */
    private bool $_setOriginal;

    /** @var array|object|null  */
    private array|object|null $_original;

    /**
     * 表示验证器是否应在第一个规则失败时停止。
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;



    public function __construct(array|object|null $data=null,bool $setOriginal=false)
    {
        $this->setOriginalData($data,$setOriginal)
            ->validateAction($data,$this->stopOnFirstFailure)
            ->beforeFillAction($data)
            ->fillAction($data)
            ->afterFillAction($data);
    }




    protected function beforeFill(){

    }

    protected function afterFill(){

    }

    /**
     * @param array|object|null $data
     * @return $this
     */
    private function setOriginalData(array|object|null $data,bool $setOriginal): self
    {
        $this->_setOriginal = $setOriginal;
        if($setOriginal){
            $this->_original = $data;
        }

        return $this;
    }


    /**
     * @return array|object|null 获取原始数据
     */
    public function getOriginalData():array|object|null{
        return $this->_original;
    }





    /**
     * @return Nemo
     */
    private function beforeFillAction(): Nemo
    {
        $this->beforeFill();
        return $this;
    }

    /**
     * @return Nemo
     */
    private function fillAction($data): Nemo
    {
        $parser =  new PropertyParser($this);
        $parser->fillData($data);
        return $this;
    }

    /**
     * @return Nemo
     */
    private function afterFillAction(): Nemo
    {
        $this->afterFill();
        return $this;
    }

    /**
     * 将数组转换成对象的方法
     * @param mixed $data
     * @return static
     */
    public static function fromItem(mixed $data): static
    {
        return new static($data);
    }

    /**
     * 将数组列表转换成对象的方法
     * @param array $list
     * @return static[]
     */
    public static function fromList(array $list):array{
        $data = [];
        foreach ($list as $item){
            $data[] = new static($item);
        }
        return $data;
    }

    /**
     * 获取属性信息
     * @param bool $parseDoc
     * @param bool $enumDoc
     * @return array
     * @throws \ReflectionException
     */
    #[ArrayShape(['Key'=>PropertyInfo::class])]
    public static function getPropertiesInfo(bool $parseDoc=false,bool $enumDoc=false): array
    {
        $parser =  new PropertyParser(null);
        return $parser->getProxyPropertyData(static::class,$parseDoc,$enumDoc);
    }

    /**
     * 获取属性列表
     * @return string[]
     * @throws Exceptions\DocumentPropertyError
     */
    public static function getProperties():array{
        $data =  self::getPropertiesInfo();
        return array_keys($data);
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function toArray():array{
        return (array)json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * @return string
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }


}
