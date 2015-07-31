<?php
/**
 * Created scottshipman
 * Date: 7/28/15
 */
namespace TUI\Toolkit\MediaBundle\EventListener;

use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PreUploadEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TUI\Toolkit\MediaBundle\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UploadListener extends Controller {
  public function __construct($doctrine)
  {
    $this->doctrine = $doctrine;
  }

  public function onUpload(PostPersistEvent $event)
  {
//  getFile: Get the uploaded file. Is either an instance of Gaufrette\File or Symfony\Component\HttpFoundation\File\File.
//  getRequest: Get the current request including custom variables.
//  getResponse: Get the response object to add custom return data.
//  getType: Get the name of the mapping of the current upload. Useful if you have multiple mappings and EventListeners.
//  getConfig: Get the config of the mapping.

    $response = $event->getResponse();
    $file = $event->getFile();
    $response['filename'] = $file->getFileName();
    $response['filepath'] = $file->getPath();
    $response['relativepath'] = '/' . strstr($response['filepath'], 'static');

    // persist object to database
    $em = $this->doctrine->getManager();
    $entity = new Media();
    $entity->setFilename($response['original_filename']);
    $entity->setHashedFilename($response['filename']);
    $entity->setContext($response['context']);
    $entity->setMimetype($response['mime_type']);
    $entity->setFilesize($response['file_size']);
    $entity->setRelativepath($response['relativepath']);
    $entity->setFilepath($response['filepath']);

    $em->persist($entity);
    $em->flush();
    $response['id'] = $entity->getId();
    $foo='';
  }

  public function preUpload(PreUploadEvent $event)
  {
    $file = $event->getFile();
    $response = $event->getResponse();
    if($file->isValid()) {
      $response['original_filename'] = $file->getClientOriginalName();
      $response['mime_type'] = $file->getMimeType();
      $response['file_size'] = $file->getClientSize();
      $response['context'] = $event->getType();
      $response['upload_success'] = true;
    }
  }
}