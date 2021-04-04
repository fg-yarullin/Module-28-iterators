<?php

class HtmlIterator implements \Iterator
{
    const ROW_SIZE = 4096;
 
    protected $filePointer = null;
 
    /**
     * Текущий элемент, который возвращается на каждой итерации.
     *
     * @var array
     */
    protected $currentElement = null;
 
    /**
     * Счётчик строк.
     *
     * @var int
     */
    protected $rowCounter = null;
  
    /**
     * Конструктор пытается открыть HTML-файл. Он выдаёт исключение при ошибке.
     *
     * @param string $file html-файл.
     * 
     * @param array $keywords
     *
     * @throws \Exception
     */
    public function __construct($file)
    {
        try {
            $this->filePointer = fopen($file, 'r+b');
        } catch (\Exception $e) {
            throw new \Exception('The file "' . $file . '" cannot be read.');
        }
    }
 
    /**
     * Этот метод сбрасывает указатель файла.
     */
    public function rewind(): void
    {
        $this->rowCounter = 0;
        rewind($this->filePointer);
    }
 
    /**
     * Этот метод возвращает текущую CSV-строку в виде двумерного массива.
     *
     * @return array Текущая CSV-строка в виде двумерного массива.
     */
    public function current()
    {
        $this->currentElement = fgets($this->filePointer, self::ROW_SIZE);
        $this->rowCounter++;
 
        return $this->currentElement;
    }
 
    /**
     * Этот метод возвращает номер текущей строки.
     *
     * @return int Номер текущей строки.
     */
    public function key(): int
    {
        return $this->rowCounter;
    }
 
    /**
     * Этот метод проверяет, достигнут ли конец файла.
     *
     * @return bool Возвращает true при достижении EOF, в ином случае false.
     */
    public function next(): bool
    {
        if (is_resource($this->filePointer)) {
            return !feof($this->filePointer);
        }
 
        return false;
    }
 
    /**
     * Этот метод проверяет, является ли следующая строка допустимой.
     *
     * @return bool Если следующая строка является допустимой.
     */
    public function valid(): bool
    {
        if (!$this->next()) {
            if (is_resource($this->filePointer)) {
                fclose($this->filePointer);
            }
 
            return false;
        }
 
        return true;
    }
}
 
/**
 * Клиентский код.
 */
$htmlCode = new HtmlIterator(__DIR__ . '/htmlcode.html');
$editedHtmlCode = fopen(__DIR__ . '/editedhtmlcode.html','w+b');

$cuttedHtmlCode = fopen(__DIR__ . '/cuttedhtmlcode.html','w+b');

foreach ($htmlCode as $key => $row) {
    $isMatched = preg_match(
        '/<+\bmeta\s\bname+=+"+keywords|description+"+[^>]*>|<+title+>+[^>]*>/',
        $row);
    if (!$isMatched) {
        print(trim($row));
        // var_dump($row);
        fwrite($editedHtmlCode, $row);
    } else {
        fwrite($cuttedHtmlCode, ltrim($row));
    }
}

fclose($editedHtmlCode, $cuttedHtmlCode);