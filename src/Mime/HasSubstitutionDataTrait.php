<?php namespace Hampel\Symfony\Mailer\SparkPost\Mime;

trait HasSubstitutionDataTrait
{
    /**
     * @var array
     */
    private $substitutionData;

    public function getSubstitutionData(): array
    {
        return $this->substitutionData ?? [];
    }

    public function setSubstitutionData(array $substitutionData): self
    {
        $this->substitutionData = $substitutionData;

        return $this;
    }

    public function addSubstitutionData(string $key, $value): self
    {
        $this->substitutionData[$key] = $value;

        return $this;
    }
}
