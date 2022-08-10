<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderRemover
{
    private string $targetDirectory;

    private SluggerInterface $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file): array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
            $result["isSuccess"] = true;
            $result["message"] = "File uploaded";
            $result["data"] = $fileName;
        } catch (FileException $e) {
            $result["message"] = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param string $imageName
     * @return array
     */
    public function deleteImage(string $imageName)
    {
        $result = ["isSuccess"=>false,"message"=>"No action taken"];
        try {
            unlink($this->targetDirectory."/".$imageName);
            $result["isSuccess"] = true;
        }catch (\Exception $exception){
             $result["message"] = $exception->getMessage();
        }
        return  $result;
    }


    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}