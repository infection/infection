<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Git\CiAwareGitIntegration;

enum Scenario
{
    case REPOSITORY_DEFAULT_BASE_OUTSIDE_CI;
    case GITHUB_PULL_REQUEST_TARGETING_NON_DEFAULT_BRANCH;
    case GITHUB_PULL_REQUEST_MERGE_CHECKOUT_WITH_DIVERGED_TARGET_BRANCH;
    case GITHUB_STACKED_PULL_REQUEST_WITH_DISTINCT_STACK_BASE;
    case GITHUB_MERGE_GROUP_TARGETING_NON_DEFAULT_BRANCH;
    case GITLAB_DETACHED_MERGE_REQUEST_WITH_DIVERGED_TARGET_BRANCH;
    case GITLAB_MERGED_RESULT_WITH_DIVERGED_TARGET_BRANCH;
    case GITLAB_MERGE_TRAIN_WITH_NEWER_TARGET_BRANCH_CHANGES;
}
