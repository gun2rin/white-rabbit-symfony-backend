<?php
/**
 *
 * This file is part of the White Rabbit application.
 *
 * (c) Vladimir Ganturin <gun2rin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rabbit\Service;


use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Rabbit\Exception\FileTransformException;


/**
 * Class FileTransformer
 * @package Rabbit\Service
 * @author Vladimir Ganturin <gun2rin@gmail.com>
 */
final class FileTransformer implements FileTransformInterface
{
    /**
     * @var \SplFileInfo
     */
    private $file;


    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var bool
     */
    private $error = false;
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $workDir = '';


    /**
     * @var string
     */
    private $downloadDir = '';

    /**
     * @var string
     */
    private $downloadLink = '';

    /**
     * @var string
     */
    private $outputFormat = '';

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private  $validator;

    /**
     * @var string
     */
    private  $imageFilePath;


    /**
     * @var string
     */
    private  $thumbFilePath;


    /**
     * @var string
     */
    private  $imageDownloadPath;

    /**
     * @var string
     */
    private  $thumbDownloadPath;


    /**
     * @var string
     */
    private  $backgroundColor;

    /**
     * @var string
     */
    private  $foregroundColor;


    /**
     * @var string
     */


    /**
     * FileTransformer constructor.
     *
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param array $params bound by framework and configured in services.yml
     *
     */
    public function __construct(ValidatorInterface $validator, array $params)
    {

        $this->validator = $validator;
        $this->params = $params;
        $this->workDir = $this->params['work_dir'];
        $this->downloadDir = $this->params['download_dir'];
        $this->downloadLink = $this->params['download_link'];
        $this->outputFormat = $this->params['output_format'];
    }


    /**
     * @param \SplFileInfo $file
     * @return \Rabbit\Service\FileTransformInterface
     */
    public function setFile(\SplFileInfo $file, array $options): FileTransformer
    {

        $this->file = $file;



        if(!file_exists($this->file->getPath()))
        {
            throw new FileTransformException();
        }

        $this->setFileNames();
        $this->setColors($options['foreground'],$options['background']);

        return $this;
    }



    private function setFileNames():void
    {


        $this->imageFilePath = $this->workDir. $this->file->getFileName() . '.'.$this->outputFormat;
        $this->thumbFilePath = $this->workDir.$this->file->getFileName().'_thumb.'.$this->outputFormat;

        $this->imageDownloadPath = $this->downloadLink. $this->file->getFileName() . '.'.$this->outputFormat;
        $this->thumbDownloadPath = $this->downloadDir.$this->file->getFileName() . '_thumb.'.$this->outputFormat;
    }


    /**
     * Validation of uploaded file with constraints
     * @param string $foreground
     * @param string $background
     * @return \Rabbit\Service\FileTransformer
     * @throws \Rabbit\Exception\FileTransformException
     */
    public function validate(): FileTransformer
    {


        $constraints = $this->getConstraints();

        $errors = $this->validator->validate($this->file,$constraints['file']);

        $errors->addAll($this->validator->validate($this->foregroundColor,$constraints['color']));
        $errors->addAll($this->validator->validate($this->backgroundColor,$constraints['color']));



        if (count($errors) !== 0) {
            $this->error = true;

            foreach ($errors as $error) {
                $this->messages[] = $error->getMessage();
            }

            throw new FileTransformException();

        }

        return $this;
    }

    /**
     * $params injected in the constructor
     * @return array
     */
    protected function getConstraints(): array
    {

        $constraints['file'] = new Assert\Image($this->params['constraints']['files']);
        $constraints['color'] = new Assert\Regex($this->params['constraints']['colors']);

        return $constraints;
    }



    /**
     * @param $foreground
     * @param $background
     */
    private function setColors(string $foreground, string $background):void
    {
        $this->backgroundColor = $background;
        $this->foregroundColor = $foreground;
    }


    /**
     * Converts image into ascii art with img2a
     *
     * @see https://github.com/EriHoss/img2a
     *
     * @return array
     * @throws \Rabbit\Exception\FileTransformException
     */
    public function transform(): array
    {


        $command = 'img2a ' . $this->file->getPathName()
            . ' --image-out-format '.$this->outputFormat
            . ' --image-background "'.$this->backgroundColor.'"'
            . ' --height 2000'
            . ' --image-foreground "'.$this->foregroundColor.'"'
            . ' --image-out ' . $this->imageFilePath;


        try {


            $process = new Process($command);
            $process->run();
            $result =  $this->prepareImage();
            $this->messages[] = $this->params['msg_success'];
            return $result;

        } catch (\Exception $e) {
            $this->error = true;
            $this->messages[] = $this->params['msg_fail'];
            throw new FileTransformException();
        }

    }



    /**
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }


    /**
     * @return array
     * @throws \Rabbit\Exception\FileTransformException
     */
    private function prepareImage():array
    {

       if(file_exists($this->imageFilePath)) {


           $this->makeThumbnail();


           $size = round(filesize($this->imageFilePath) / 1048576,2);

           return [
               'thumb' => $this->thumbDownloadPath,
               'image' => $this->imageDownloadPath,
               'fileSize' => $size.' MB'
           ];
       }

       throw new FileTransformException();
    }


    /* Resize the transformed image and makes thumbnail
     *
     * @throws \Rabbit\Exception\FileTransformException
     */
    private function makeThumbnail():void
    {

        $imagick = new \Imagick($this->imageFilePath);

        $imagick->resizeImage(450,450 ,\Imagick::FILTER_LANCZOS, 1, TRUE);
        $result = $imagick->writeImage($this->thumbFilePath);
        $imagick->clear();
        $imagick->destroy();


        if($result !== true) {
            throw new FileTransformException();
        }

    }

    /**
     * Validation error messages
     * @return array
     * @see \Rabbit\Service\FileTransformer::validate()
     */
    public function getMessages(): array
    {

        return $this->messages;

    }

}