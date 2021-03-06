<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of contemplate (https://github.com/ehough/contemplate)
 *
 * contemplate is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * contemplate is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with contemplate.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Builds template instances.
 */
interface ehough_contemplate_api_TemplateBuilder
{
    /**
     * Get a new template instance.
     *
     * @param string $path The absolute path of the template.
     *
     * @return ehough_contemplate_api_Template The template instance.
     *
     * @throws ehough_contemplate_api_exception_InvalidArgumentException If the given file does not exist.
     */
    function getNewTemplateInstance($path);
}