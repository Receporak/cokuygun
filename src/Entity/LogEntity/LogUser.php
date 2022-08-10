<?php


namespace App\Entity\LogEntity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * Gedmo\Loggable\Entity\LogEntry
 *
 * @ORM\Table(
 *     name="log_user",
 *     options={"row_format":"DYNAMIC","collate"="utf8_general_ci"},
 *  indexes={
 *      @ORM\Index(name="log_class_lookup_idx", columns={"object_class"}),
 *      @ORM\Index(name="log_date_lookup_idx", columns={"logged_at"}),
 *      @ORM\Index(name="log_user_lookup_idx", columns={"username"}),
 *      @ORM\Index(name="log_version_lookup_idx", columns={"object_id", "object_class", "version"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class LogUser extends AbstractLogEntry
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
