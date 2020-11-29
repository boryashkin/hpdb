<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="HPDB api",
 *     version="1.0",
 *     @OA\Contact(
 *         url="https://borisd.ru",
 *         email="hpdb@borisd.ru"
 *     )
 * ),
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     securityScheme="apiKey",
 *     name="Authorization",
 *     description="Generate a token in /api/v1/rpc/auth"
 * ),
 * @OA\Tag(
 *     name="feed"
 * ),
 * @OA\Tag(
 *     name="group"
 * ),
 * @OA\Tag(
 *     name="profile"
 * ),
 * @OA\Tag(
 *     name="reaction"
 * ),
 * @OA\Tag(
 *     name="rpc"
 * )
 */
