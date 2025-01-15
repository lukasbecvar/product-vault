<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class LogRepository
 *
 * Repository for log database entity
 *
 * @extends ServiceEntityRepository<Log>
 *
 * @package App\Repository
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Get logs by status with pagination
     *
     * @param string $status The status of the logs
     * @param int $page The page number
     * @param int $limit The limit of logs per page (default: 50)
     *
     * @return array<Log> Logs list
     */
    public function findByStatus(string $status, int $page, int $limit = 50): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', $status)
            ->orderBy('l.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get logs by user id with pagination
     *
     * @param int $userId The user id
     * @param int $page The page number
     * @param int $limit The limit of logs per page (default: 50)
     *
     * @return array<Log> Logs list
     */
    public function findByUserId(int $userId, int $page, int $limit = 50): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.user_id = :user_id')
            ->setParameter('user_id', $userId)
            ->orderBy('l.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get logs by ip address with pagination
     *
     * @param string $ipAddress The ip address
     * @param int $page The page number
     * @param int $limit The limit of logs per page (default: 50)
     *
     * @return array<Log> Logs list
     */
    public function findByIpAddress(string $ipAddress, int $page, int $limit = 50): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.ip_address = :ip_address')
            ->setParameter('ip_address', $ipAddress)
            ->orderBy('l.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get logs pagination info
     *
     * @param string $status The status of the logs
     * @param int $currentPage The current page number
     * @param int $limit The limit of logs per page (default: 50)
     *
     * @return array<string, int|bool> Pagination info
     */
    public function getPaginationInfo(string $status, int $currentPage, ?int $limit): array
    {
        // set default limit if not provided
        if ($limit === null) {
            $limit = 50;
        }

        $queryBuilder = $this->createQueryBuilder('log')
            ->select('COUNT(log.id) as totalLogs')
            ->where('log.status = :status')
            ->setParameter('status', $status);

        // get pagination info data
        $totalLogsCount = (int) $queryBuilder->getQuery()->getSingleScalarResult();
        $totalPagesCount = (int) ceil($totalLogsCount / $limit);
        $isNextPageExists = $currentPage < $totalPagesCount;
        $isPreviousPageExists = $currentPage > 1;
        $lastPageNumber = $totalPagesCount;

        return [
            'total_logs_count' => $totalLogsCount,
            'current_page' => $currentPage,
            'total_pages_count' => $totalPagesCount,
            'is_next_page_exists' => $isNextPageExists,
            'is_previous_page_exists' => $isPreviousPageExists,
            'last_page_number' => $lastPageNumber,
        ];
    }
}
