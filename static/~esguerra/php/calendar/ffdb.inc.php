<?php
//------------------------------------------------------------------------
// File:      ffdb.inc.php
// Version:   2.7
// Author:    John Papandriopoulos <jpap@users.sourceforge.net>
// WWW home:  http://ffdb-php.sourceforge.net/
//
// Copyright (C) 2002 John Papandriopoulos.  All rights reserved.
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
//------------------------------------------------------------------------

/*! 
 * @header ffdb
 * @abstract Flat File DataBase Library
 * @discussion Advanced PHP database for those without mySQL or similar 
 * dedicated databases. Supports many data types and advanced features through 
 * a simple PHP object API. Low-level implementation very efficient with 
 * indexes.
 */


/*!
 * @defined FFDB_VERSION_MAJOR
 * @discussion Major version of the FFDB package
 */
define("FFDB_VERSION_MAJOR", 2);


/*!
 * @defined FFDB_VERSION_MINOR
 * @discussion Minor version of the FFDB package
 */
define("FFDB_VERSION_MINOR", 7);


/*!
 * @defined FFDB_VERSION
 * @discussion Version of the FFDB package
 */
define("FFDB_VERSION", FFDB_VERSION_MAJOR.".".FFDB_VERSION_MINOR);

/*!
 * @defined FFDB_INT
 * @discussion Flat File DataBase int type
 */
define("FFDB_INT", 0);

/*!
 * @defined FFDB_INT_AUTOINC
 * @discussion Flat File DataBase automatically incremented int type
 */
define("FFDB_INT_AUTOINC", 5);

/*!
 * @defined FFDB_STRING
 * @discussion Flat File DataBase string type
 */
define("FFDB_STRING", 1);

/*!
 * @defined FFDB_ARRAY
 * @discussion Flat File DataBase array type
 */
define("FFDB_ARRAY", 2);

/*!
 * @defined FFDB_FLOAT
 * @discussion Flat File DataBase float type
 */
define("FFDB_FLOAT", 3);

/*!
 * @defined FFDB_BOOL
 * @discussion Flat File DataBase boolean type
 */
define("FFDB_BOOL", 4);


/*!
 * @defined FFDB_IFIELD
 * @discussion Index field used when returning records.  See the 
 * getbyfield(...), getbyfunction(...) and getall(...) methods.
 */
define("FFDB_IFIELD", "FFDB_IFIELD");


/*!
 * @defined FFDB_SIGNATURE
 * @discussion Database signature placed at the start of each index file.
 * Internal use only.
 */
define("FFDB_SIGNATURE", 0x42444646 /* "FFDB" in hex */);


/*!
 * @defined FFDB_INDEX_VERSION_OFFSET
 * @discussion Location of the 'version' offset in the FFDB index.
 * Internal use only.
 */
define("FFDB_INDEX_VERSION_OFFSET", 4);


/*!
 * @defined FFDB_INDEX_RECORDS_OFFSET
 * @discussion Location of the 'records count' offset in the FFDB index.
 * Internal use only.
 */
define("FFDB_INDEX_RECORDS_OFFSET", 6);


/*!
 * @defined FFDB_INDEX_DELETED_OFFSET
 * @discussion Location of the 'deleted count' offset in the FFDB index.
 * Always the next 'int size' offset after the 'records count' offset.
 * Internal use only.
 */
define("FFDB_INDEX_DELETED_OFFSET", FFDB_INDEX_RECORDS_OFFSET+4);


/*!
 * @defined FFDB_INDEX_RBLOCK_SIZE
 * @discussion Size of the field specifing the size of a record in the index.
 * Internal use only.
 */
define("FFDB_INDEX_RBLOCK_SIZE", 4 /* int */);


/*! 
 * @class FFDB
 * @abstract Flat File DataBase Class
 * @discussion Implements a flat file database.
 */
class FFDB
{
   var $isopen;
   var $dbname;
   var $data_fp;
   var $meta_fp;
   var $records;
   var $deleted;
   var $locked;
   var $auto_clean;

   var $fields;
   var $autoinc;
   var $primary_key;
   var $index_start;

   
   /*!
    * @function FFDB
    * @abstract Constructor
    */
   function FFDB()
   {
      // Disable auto-clean by default
      $this->auto_clean = -1;

      // Database hasn't been opened yet...
      $this->isopen = false;

      // Ignore user aborts that might corrupt the database
      ignore_user_abort(true);
   }

   
   /*!
    * @function open
    * @abstract Opens the given database
    * @param dbname  string - The name of the database to open
    * @result bool - true if successful, false if failed
    */
   function open($dbname)
   {
      // Close existing databases first
      if ($this->isopen)
         $this->close();
      
      // Open the database files
      $this->data_fp = @fopen($dbname.".dat", "rb+");
      if ($this->data_fp === false)
      {
         // user_error("Cannot open data file: $dbname.dat", E_USER_ERROR);
         return false;
      }

      $this->meta_fp = @fopen($dbname.".met", "rb+");
      if (!$this->meta_fp)
      {
         fclose($this->data_fp);
         // user_error("Cannot open meta file: $dbname.met", E_USER_ERROR);
         return false;
      }

      $this->forcelock = 0;
      $this->locked = 0;
      $this->isopen = true;
      $this->dbname = $dbname;
      
      if (!$this->lock_read())
         return false;

      // Read and verify the signature
      $sig = $this->read_int($this->meta_fp);
      if ($sig != FFDB_SIGNATURE)
      {
         $this->unlock();
         user_error("Invalid database: $dbname.", E_USER_ERROR);
         return false;
      }

      // Read the version
      $ver_major = $this->read_byte($this->meta_fp);
      $ver_minor = $this->read_byte($this->meta_fp);

      // Make sure we only read databases of the same major version,
      // with minor version less or equal to the current.
      if ($ver_major != FFDB_VERSION_MAJOR)
      {
         $this->unlock();
         user_error(
            "Cannot open database (of version $ver_major.$ver_minor), "
           ."wrong version.", 
            E_USER_ERROR
         );
         return false;
      }
      if ($ver_minor > FFDB_VERSION_MINOR)
      {
         $this->unlock();
         user_error(
            "Cannot open database (of version $ver_major.$ver_minor), "
           ."wrong version.", 
            E_USER_ERROR
         );
         return false;
      }

      // Read the schema and database statistics from the meta file.
      $this->read_schema();

      $this->unlock();

      return true;
   }

   
   /*!
    * @function close
    * @abstract Closes the currently opened database
    */
   function close()
   {
      if ($this->isopen)
      {
         @fclose($this->data_fp);
         @fclose($this->meta_fp);
         $this->isopen = false;
      }
   }


   /*!
    * @function drop
    * @abstract Closes the current database then deletes it.
    * @result bool - true on success
    */
   function drop()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      $this->close();
      @unlink($this->dbname.".dat");
      @unlink($this->dbname.".met");

      return true;
   }


   /*!
    * @function create
    * @abstract Creates a new database
    * @param dbname  string - name of the database
    * @param schema  array - name<->type array of fields for table.
    * Note that the key cannot be an array or boolean type field.  
    * The key is given by a third attribute - a string "key".
    * @result bool - true if successful, false on failure
    */
   function create($dbname, $schema)
   {
      // Close any existing DB first
      if ($this->isopen)
         $this->close();

      // Find the primary key and do error checking on the schema
      $this->fields = array();
      $this->autoinc = array();
      $this->primary_key = "";

      for($i=0; $i<count($schema); ++$i)
      {
         $field = $schema[$i];

         if (!is_array($field))
            return false;

         $name = &$field[0];
         $type = &$field[1];

         // Make sure the name of the field is a string
         if (!is_string($name))
            return false;

         // Make sure the field type is one of our constants
         if (!is_int($type))
            return false;

         switch($type)
         {
            case FFDB_INT:
            case FFDB_STRING:
            case FFDB_ARRAY:
            case FFDB_FLOAT:
            case FFDB_BOOL:
               break;

            case FFDB_INT_AUTOINC:
               // Set up the default starting value for an auto-inc'ed field
               $this->autoinc[$name] = 0;
               break;

            default:
               // Unknown type...!
               user_error(
                  "Invalid type in schema (found $type).", 
                  E_USER_ERROR
               );
               return false;
         }

         if (count($field) == 3)
         {
            $keyword = &$field[2];

            if ( ($type == FFDB_INT_AUTOINC) && is_int($keyword) )
            {
               // Auto-increment starting value
               $this->autoinc[$name] = $keyword;
            }
            else if ( ($keyword == "key") && ($this->primary_key == "") )
            {
               // Primary key!

               // Is the key an array or boolean?  
               // If so, don't allow them to be primary keys...
               switch ($type)
               {
                  case FFDB_ARRAY:
                  case FFDB_BOOL:
                     return false;
               }

               $this->primary_key = $name;
            }
            else
               return false;
         }
         else if (count($field) == 4)
         {
            $start = &$field[2];
            $keyword = &$field[3];

            // This MUST be a starting-value & "key" keyword 
            // combination (in that order).
            if ( ($type == FFDB_INT_AUTOINC) && 
                 is_int($start) && 
                 ($keyword == "key") &&
                 ($this->primary_key == "") )
            {
               // Found an auto-increment starting value
               $this->autoinc[$name] = $start;

               // Found a primary key
               $this->primary_key = $name;
            }
            else
               return false;
         }

         $this->fields[$field[0]] = $field[1];
      }

      // Create the database files
      $this->meta_fp = @fopen($dbname.".met", "wb+");
      if (!$this->meta_fp) 
      {
         user_error("Cannot create meta file: $dbname.met", E_USER_ERROR);
         return false;
      }

      $this->data_fp = @fopen($dbname.".dat", "wb+");
      if (!$this->data_fp)
      {
         fclose($this->meta_fp);
         user_error("Cannot create data file: $dbname.dat", E_USER_ERROR);
         return false;
      }

      $this->forcelock = 0;
      $this->locked = 0;
      $this->isopen = true;
      $this->dbname = $dbname;

      if (!$this->lock_write())
         return false;

      $this->records = 0;
      $this->deleted = 0;

      // Write the signature
      $this->write_int($this->meta_fp, FFDB_SIGNATURE);

      // Write the version
      $this->write_byte($this->meta_fp, FFDB_VERSION_MAJOR);
      $this->write_byte($this->meta_fp, FFDB_VERSION_MINOR);
      
      // Write the schema to the meta file
      $this->write_schema();

      $this->unlock();

      return true;
   }


   /*!
    * @function autoclean
    * @abstract Configures autoclean.  When an edit or delete is made, the
    * record is normally not removed from the data file - only the index.
    * After repeated edits/deletions, the data file may become very big with
    * dirty (non-removed) records.  A cleanup is normally done with the
    * cleanup() method.  Autoclean will do this automatically, keeping the
    * number of dirty records to under the $threshold value.
    * To turn off autoclean, set $threshold to a negative value.
    * @param threshold  - number of dirty records to have at any one time.
    */
   function autoclean($threshold = -1)
   {
      $this->auto_clean = $threshold;

      // Do an auto-cleanup if required
      if ( ($this->auto_clean >= 0) && ($this->isopen) )
         $this->automatic_cleanup();
   }


   /*!
    * @function add
    * @abstract Adds an entry to the database
    * @param record  array - record to add to the database
    * @result bool - true on success, false on failure
    */
   function add(&$record)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Verify record as compared to the schema
      foreach($this->fields as $key => $type)
      {
         // We don't mind if they include a FFDB_INT_AUTOINC field,
         // as we determine its value in any case.
         if ($type == FFDB_INT_AUTOINC)
            continue;

         // Ensure they have included an entry for each record field
         if (!$this->key_exists_array($key, $record))
         {
            user_error("Missing field during add: $key", E_USER_ERROR);
            return false;
         }

         // Verify the type
         switch($type)
         {
            case FFDB_INT:
               if (!is_int($record[$key]))
               {
                  user_error(
                     "Invalid int value field during add: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_STRING:
               if (!is_string($record[$key]))
               {
                  user_error(
                     "Invalid string value field during add: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_ARRAY:
               if (!is_array($record[$key]))
               {
                  user_error(
                     "Invalid array value for field during add: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;

            case FFDB_FLOAT:
               if (!is_float($record[$key]))
               {
                  user_error(
                     "Invalid float value for field during add: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_BOOL:
               if (!is_bool($record[$key]))
               {
                  user_error(
                     "Invalid bool value for field during add: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;

            default:
               // Unknown type...!
               user_error(
                  "Invalid type in record during add (found $type).", 
                  E_USER_ERROR
               );
               return false;
         }
      }

      // Add the item to the data file
      if (!$this->lock_write())
         return false;

      // Add in the auto-incremented field if required
      if (count($this->autoinc) > 0)
      {
         // Get the latest copy of the auto-inc values
         $this->read_schema();

         // Now update those values in our record
         foreach($this->fields as $key => $type)
            if ($type == FFDB_INT_AUTOINC)
               $record[$key] = $this->autoinc[$key]++;

         // Write out the newly updated auto-inc values
         $this->write_schema();
      }

      fseek($this->data_fp, 0, SEEK_END);
      $new_offset = ftell($this->data_fp);

      // Write the index.  To enable a binary search, we must read in the 
      // entire index, add in our item, sort it, then write it back out.
      // Where there is no primary key, we can't do a binary search so skip
      // this sorting business.

      if ($this->primary_key != "")
      {
         if ($this->records > 0)
         {
            $index = $this->read_index();
            if (!$index)
            {
               // Error reading index
               $this->unlock();
               return false;
            }

            // Do a binary search to find the insertion position
            $pos = $this->bsearch(
               $index, 
               0, 
               $this->records-1, 
               $record[$this->primary_key]
            );

            // Ensure we don't have a duplicate key in the database
            if ($pos > 0)
            {
               // Oops... duplicate key
               //user_error("Duplicate database key during add", E_USER_ERROR);
               return false;
            }

            // Revert the result from bsearch to the proper insertion position
            $pos = (-$pos)-1;

            // Shuffle all of the items up by one to make room for the new item
            for($i=$this->records; $i>$pos; --$i)
               $index[$i] = $index[$i-1];

            // Insert the new item to the correct position
            $index[$pos] = $new_offset;
         }
         else
         {
            $index[0] = $new_offset;
         }

         // We have a new entry
         ++$this->records;

         // Write the index back out to the file
         $this->write_index($index);
      }
      else
      {
         // We have a new entry
         ++$this->records;

         // Add an entry into the index
         fseek($this->meta_fp, 0, SEEK_END);
         $this->write_int($this->meta_fp, $new_offset);
      }

      // Write the number of records to the meta data file
      fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);
      $this->write_int($this->meta_fp, $this->records);

      // Write the record to the end of the database file
      fseek($this->data_fp, $new_offset, SEEK_SET);
      if (!$this->write_record($this->data_fp, $record))
      {
         // Error writing item to the database
         $this->unlock();
         return false;
      }

      $this->unlock();

      return true;
   }


   /*!
    * @function removebykey
    * @abstract Removes an entry from the database INDEX only - it appears
    * deleted, but the actual data is only removed from the file when a 
    * cleanup() is called.
    * @param key  primary key used to identify record to remove.  For
    * databases without primary keys, it is the record number (zero based) in
    * the table.
    * @result bool - true on success, false on failure
    */
   function removebykey($key)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($this->records == 0)
      {
         // user_error("No items in database.", E_USER_ERROR);
         return false;
      }

      // All we do here is remove the item from the index.
      // Read in the index, check to see if it exists, delete the item,
      // then rebuild the index on disk.

      if (!$this->lock_write())
         return false;

      $index = $this->read_index();
      if (!$index)
      {
         // Error reading index
         $this->unlock();
         return false;
      }

      if ($this->primary_key != "")
      {
         // Do a binary search to find the item
         $pos = $this->bsearch($index, 0, $this->records-1, $key);

         if ($pos < 0)
         {
            // Not found!
            $this->unlock();
            return false;
         }

         // Revert the result from bsearch to the proper insertion position
         --$pos;

         // Shuffle all of the items down by one to remove the item
         for($i=$pos; $i<$this->records-1; ++$i)
            $index[$i] = $index[$i+1];

         // Kill the last array item
         array_pop($index);
      }
      else
      {
         // Ensure the "key" is the item number
         if (!is_int($key))
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         // Ensure it is within range
         if ( ($key<0) || ($key>$this->records-1) )
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         // Shuffle all of the items down by one to remove the item
         for($i=$key; $i<$this->records-1; ++$i)
            $index[$i] = $index[$i+1];

         // Kill the last array item
         array_pop($index);
      }

      fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

      // Write the number of records to the meta data file
      $this->write_int($this->meta_fp, --$this->records);

      // Write the number of (unclean) deleted records to the meta data file
      $this->write_int($this->meta_fp, ++$this->deleted);

      // Write the index back out to the file
      $this->write_index($index);

      // Do an auto-cleanup if required
      if ($this->auto_clean >= 0)
         $this->automatic_cleanup();

      $this->unlock();

      return true;
   }


   /*!
    * @function removebyindex
    * @abstract Removes an entry from the database INDEX only - it appears
    * deleted, but the actual data is only removed from the file when a 
    * cleanup() is called.
    * @param record_num  The record number (zero based) in the table to remove.
    * @result bool - true on success, false on failure
    */
   function removebyindex($record_num)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($this->records == 0)
      {
         // user_error("No items in database.", E_USER_ERROR);
         return false;
      }

      // All we do here is remove the item from the index.
      // Read in the index, check to see if it exists, delete the item,
      // then rebuild the index on disk.

      if (!$this->lock_write())
         return false;

      $index = $this->read_index();
      if (!$index)
      {
         // Error reading index
         $this->unlock();
         return false;
      }

      // Make sure the record number is an int
      if (!is_int($record_num))
      {
         $this->unlock();
         user_error("Invalid record number ($record_num).", E_USER_ERROR);
         return false;
      }

      // Ensure it is within range
      if ( ($record_num<0) || ($record_num>$this->records-1) )
      {
         $this->unlock();
         user_error("Invalid record number ($record_num).", E_USER_ERROR);
         return false;
      }

      // Shuffle all of the items down by one to remove the item
      for($i=$record_num; $i<$this->records-1; ++$i)
         $index[$i] = $index[$i+1];

      // Kill the last array item
      array_pop($index);

      fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

      // Write the number of records to the meta data file
      $this->write_int($this->meta_fp, --$this->records);

      // Write the number of (unclean) deleted records to the meta data file
      $this->write_int($this->meta_fp, ++$this->deleted);

      // Write the index back out to the file
      $this->write_index($index);

      // Do an auto-cleanup if required
      if ($this->auto_clean >= 0)
         $this->automatic_cleanup();

      $this->unlock();

      return true;
   }


   /*!
    * @function removebyfield
    * @abstract Removes entries from the database INDEX only, based on the
    * result of a regular expression match on a given field - records appear 
    * deleted, but the actual data is only removed from the file when a 
    * cleanup() is called.
    * @param fieldname  the field which to do matching on
    * @param regex  the regular expression to match a field on.
    * Note: you should include the delimiters ("/php/i" for example).
    * @result int - number of records removed (or bool/false on failure).
    */
   function removebyfield($fieldname, $regex)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($this->records == 0)
         return 0;

      // Read in each record once at a time, and remove it from
      // the index if the select function determines it to be deleted.
      // Rebuild the index on disc if there items were deleted.
      $delete_count = 0;

      if (!$this->lock_write())
         return false;

      $index = $this->read_index();
      if (!$index)
      {
         // Error reading index
         $this->unlock();
         return false;
      }

      // Read and delete selected records
      for($record_num=0; $record_num<$this->records; ++$record_num)
      {
         // Read the record
         list($record, $rsize) 
            = $this->read_record($this->data_fp, $index[$record_num]);

         // Remove the record if the field matches the regular expression
         if (preg_match($regex, $record[$fieldname]))
         {
            // Delete this item from the index.
            // Shuffle all of the items down by one to remove the item.
            for($i=$record_num; $i<$this->records-1; ++$i)
               $index[$i] = $index[$i+1];
            
            // Kill the last index item that was duplicated
            array_pop($index);

            --$this->records;
            ++$this->deleted;

            // Make sure we don't skip over the next item in the for() loop
            --$record_num;

            ++$delete_count;
         }
      }

      if ($delete_count > 0)
      {
         fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

         // Write the number of records to the meta data file
         $this->write_int($this->meta_fp, $this->records);

         // Write the number of (unclean) deleted records to the meta data file
         $this->write_int($this->meta_fp, $this->deleted);

         // Write the index back out to the file
         $this->write_index($index);

         // Do an auto-cleanup if required
         if ($this->auto_clean >= 0)
            $this->automatic_cleanup();
      }

      $this->unlock();

      return $delete_count;
   }


   /*!
    * @function removebyfunction
    * @abstract Removes entries from the database INDEX only, based on the
    * result of a user-specified function - records appear deleted, but the 
    * actual data is only removed from the file when a  cleanup() is called.
    * @param selectfn  the function that accepts one argument (an array record),
    * and returns true or false.
    * @result int - number of records removed (or bool/false on failure).
    */
   function removebyfunction($selectfn)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($this->records == 0)
         return 0;

      // Read in each record once at a time, and remove it from
      // the index if the select function determines it to be deleted.
      // Rebuild the index on disc if there items were deleted.
      $delete_count = 0;

      if (!$this->lock_write())
         return false;

      $index = $this->read_index();
      if (!$index)
      {
         // Error reading index
         $this->unlock();
         return false;
      }

      // Read and delete selected records
      for($record_num=0; $record_num<$this->records; ++$record_num)
      {
         // Read the record
         list($record, $rsize) 
            = $this->read_record($this->data_fp, $index[$record_num]);

         // Remove the record if the $selectfn OK's it
         if ($selectfn($record) == true)
         {
            // Delete this item from the index.
            // Shuffle all of the items down by one to remove the item.
            for($i=$record_num; $i<$this->records-1; ++$i)
               $index[$i] = $index[$i+1];
            
            // Kill the last index item that was duplicated
            array_pop($index);

            --$this->records;
            ++$this->deleted;

            // Make sure we don't skip over the next item in the for() loop
            --$record_num;

            ++$delete_count;
         }
      }

      if ($delete_count > 0)
      {
         fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

         // Write the number of records to the meta data file
         $this->write_int($this->meta_fp, $this->records);

         // Write the number of (unclean) deleted records to the meta data file
         $this->write_int($this->meta_fp, $this->deleted);

         // Write the index back out to the file
         $this->write_index($index);

         // Do an auto-cleanup if required
         if ($this->auto_clean >= 0)
            $this->automatic_cleanup();
      }

      $this->unlock();

      return $delete_count;
   }


   /*!
    * @function edit
    * @abstract Replaces an existing record with the same primary 
    * key as the new record.
    * @param record  record that will replace an existing one
    * @param record_num  record number to replace: ONLY for databases without
    * a primary key.  Ignored for databases WITH a primary key.
    * @result bool - true on success, false on failure
    */
   function edit(&$record, $record_num = -1)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($this->records == 0)
         return false;

      // Verify record as compared to the schema
      foreach($this->fields as $key => $type)
      {
         // Ensure they have included an entry for each record field
         if (!$this->key_exists_array($key, $record))
         {
            user_error("Missing field during edit: $key", E_USER_ERROR);
            return false;
         }

         // Verify the type
         switch($type)
         {
            case FFDB_INT_AUTOINC:
            case FFDB_INT:
               if (!is_int($record[$key]))
               {
                  user_error(
                     "Invalid int value for field during edit: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_STRING:
               if (!is_string($record[$key]))
               {
                  user_error(
                     "Invalid string value for field during edit: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_ARRAY:
               if (!is_array($record[$key]))
               {
                  user_error(
                     "Invalid array value for field during edit: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;

            case FFDB_FLOAT:
               if (!is_float($record[$key]))
               {
                  user_error(
                     "Invalid float value for field during edit: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;
            
            case FFDB_BOOL:
               if (!is_bool($record[$key]))
               {
                  user_error(
                     "Invalid bool value for field during edit: $key", 
                     E_USER_ERROR
                  );
                  return false;
               }
               break;

            default:
               // Unknown type...!
               user_error(
                  "Invalid type in record during edit (found $type).", 
                  E_USER_ERROR
               );
               return false;
         }
      }

      if (!$this->lock_write())
         return false;

      $index = $this->read_index();
      if (!$index)
      {
         // Error reading index
         $this->unlock();
         return false;
      }

      // Get the position of a new record in the database store
      fseek($this->data_fp, 0, SEEK_END);
      $new_offset = ftell($this->data_fp);


      // Re-jiggle the index.
      if ($this->primary_key != "")
      {
         // Do a binary search to find the index position
         $pos = $this->bsearch(
            $index, 
            0, 
            $this->records-1, 
            $record[$this->primary_key]
         );

         // Ensure the item to edit IS in the database, 
         // as the new one takes its place.
         if ($pos < 0)
         {
            // Oops... record wasn't found
            user_error(
               "Existing record not found in database for edit.", 
               E_USER_ERROR
            );
            return false;
         }

         // Revert the result from bsearch to the proper position
         $record_num = $pos-1;
      }
      else
      {
         // Ensure the record number is within range
         if ( ($record_num<0) || ($record_num>$this->records-1) )
         {
            $this->unlock();
            user_error("Invalid record number ($record_num).", E_USER_ERROR);
            return false;
         }
      }

      // Read the size of the record.  If it is the same or bigger than 
      // the new one, then we can just place it in its original position
      // and not worry about a dirty record.
      fseek($this->data_fp, $index[$record_num], SEEK_SET);
      $hole_size = $this->read_int($this->data_fp);

      // Get the size of the new record for calculateions below.
      $new_size = $this->record_size($record);

      if ($new_size > $hole_size)
      {
         // Record is too big for the "hole".
         //
         // Set the index to the newly edited record.  
         // The old one will be removed on the next cleanup.
         $index[$record_num] = $new_offset;

         // Write the index back out to the file
         $this->write_index($index);

         // We have a new dirty entry (the old record)
         ++$this->deleted;

         // Write the number of deleted records to the meta data file
         fseek($this->meta_fp, FFDB_INDEX_DELETED_OFFSET, SEEK_SET);
         $this->write_int($this->meta_fp, $this->deleted);

         // Place the edited record at the end of the .dat file
         fseek($this->data_fp, 0, SEEK_END);
      }
      else
      {
         // Size of "hole" is big enough.  
         // Replace it and avoid a dirty record.
         fseek($this->data_fp, $index[$record_num], SEEK_SET);
      }

      // Write the record to the database file
      if (!$this->write_record($this->data_fp, $record, $hole_size))
      {
         // Error writing item to the database
         $this->unlock();
         return false;
      }

      // Do an auto-cleanup if required
      if ( ($new_size > $hole_size) && ($this->auto_clean >= 0) )
         $this->automatic_cleanup();

      $this->unlock();

      return true;
   }


   /*!
    * @function cleanup
    * @abstract Cleans up the database by removing deleted entries
    * from the flat file.
    * @result true if successful, false otherwise
    */
   function cleanup()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Don't bother if the database is clean
      if ($this->deleted == 0)
         return true;

      if (!$this->lock_write())
         return false;

      // Read in the index, and rebuild it along with the database data
      // into a separate file.  Then move that new file back over the old
      // database.

      // Note that we attempt the file creation under the DB lock, so
      // that another process doesn't try to create the same file at the
      // same time.
      $tmpdb = @fopen($this->dbname.".tmp", "wb+");
      if (!$tmpdb)
      {
         $this->unlock();
         user_error("Cannot create temporary file.", E_USER_ERROR);
         return false;
      }
      
      // Read in the index
      $index = $this->read_index();

      // For each item in the index, move it from the current database
      // file to the new one.  Also update the new file offset in the index
      // so we can write it back out to the index file.
      for($i=0; $i<$this->records; ++$i)
      {
         $offset = $index[$i];

         // Read in the entire record
         unset($record);
         list($record, $rsize) = $this->read_record($this->data_fp, $offset);

         // Save the new file offset
         $index[$i] = ftell($tmpdb);

         // Write out the record to the temporary file
         if (!$this->write_record($tmpdb, $record)) 
         {
            // Error writing item to the database
            fclose($tmpdb);
            @unlink($this->dbname.".tmp");
            $this->unlock();
            return false;
         }
      }

      // Move the temporary file over the original database file.
      fclose($tmpdb);
      fclose($this->data_fp);
      @unlink($this->dbname.".dat");
      @rename($this->dbname.".tmp", $this->dbname.".dat");

      // Set the number of (unclean) deleted items to zero
      $this->deleted = 0;
      fseek($this->meta_fp, FFDB_INDEX_DELETED_OFFSET, SEEK_SET);
      $this->write_int($this->meta_fp, $this->deleted);

      // Rewrite the database index
      $this->write_index($index);

      // Re-open the database data file
      $this->data_fp = @fopen($this->dbname.".dat", "rb+");
      if (!$this->data_fp) 
      {
         $this->unlock();
         return false;
      }

      $this->unlock();

      return true;
   }


   /*!
    * @function reset
    * @abstract Reset the internal pointer used for iterating over records
    */
   function reset()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if (!$this->lock_read())
         return false;
      
      $this->iterator = $this->index_start;
      fseek($this->meta_fp, $this->iterator, SEEK_SET);

      $this->unlock();

      return true;
   }


   /*!
    * @function current
    * @abstract Return the current record in the database.  Note that the 
    * current iterator pointer is not moved in any way.
    * @result the current database record, or false if there are no 
    * more items left.
    */
   function current()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // No items?
      if ($this->records == 0)
         return false;

      if (!$this->lock_read())
         return false;

      // Offset of record to read
      $offset = $this->read_int($this->meta_fp);

      // No more items left?
      if (feof($this->meta_fp))
      {
         $this->unlock();
         return false;
      }

      // Restore the index position
      fseek($this->meta_fp, -4, SEEK_CUR);

      // Read in the entire record
      list($record, $rsize) = $this->read_record($this->data_fp, $offset);

      $this->unlock();

      // Return the record
      return $record;
   }


   /*!
    * @function next
    * @abstract Move the current iterator pointer to the next database item.
    * @result bool - true if advanced to a new item, false if there are 
    * none left.
    */
   function next()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // No items?
      if ($this->records == 0)
         return false;

      if (!$this->lock_read())
         return false;

      // Advance the pointer...
      $this->read_int($this->meta_fp);

      // Read another byte, to push uss over the EOF if it's there.
      // Seems to be a stupid problem with feof(...)
      $this->read_byte($this->meta_fp);

      $result = !feof($this->meta_fp);

      // Back up that extra byte we read.
      fseek($this->meta_fp, -1, SEEK_CUR);

      $this->unlock();
      
      return $result;
   }


   /*!
    * @function getbykey
    * @abstract retrieves a record based on the specified key
    * @param key  primary key used to identify record to retrieve.  For
    * databases without primary keys, it is the record number (zero based) in 
    * the table.
    * @param includeindex  if true, an extra field called 'FFDB_IFIELD' will
    * be added to each record returned.  It will contain an int that specifies
    * the original position in the database (zero based) that the record is 
    * positioned.  It might be useful when an orderby is used, and an future 
    * operation on a record is required, given it's index in the table.
    * @result record if found, or false otherwise
    */
   function getbykey($key, $includeindex = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();
      
      if ($this->primary_key != "")
      {
         // Do a binary search to find the item
         $pos = $this->bsearch($index, 0, $this->records-1, $key);

         if ($pos < 0)
         {
            // Not found!
            $this->unlock();
            return false;
         }

         // bsearch always returns the real position + 1
         --$pos;

         // Get the offset of the record in the database
         $offset = $index[$pos];

         // Save the record number
         $rcount = $pos;
      }
      else
      {
         // Ensure the record number is an int
         if (!is_int($key))
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         // Ensure the record number is within range
         if ( ($key<0) || ($key>$this->records-1) )
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         $offset = $index[$key];

         // The record number is the key...
         $rcount = $key;
      }

      // Read the record
      list($record, $rsize) = $this->read_record($this->data_fp, $offset);

      // Add the index field if required
      if ($includeindex)
         $record[FFDB_IFIELD] = $rcount;

      $this->unlock();

      return $record;
   }


   /*!
    * @function getbyindex
    * @abstract retrieves a record based on the record number in the table
    * (zero based)
    * @param record_num  zero based record number to retrieve
    * @result record if found, or false otherwise
    */
   function getbyindex($record_num)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Ensure the record number is an int
      if (!is_int($record_num))
      {
         $this->unlock();
         user_error("Invalid record number ($record_num).", E_USER_ERROR);
         return false;
      }

      // Ensure the record number is within range
      if ( ($record_num<0) || ($record_num>$this->records-1) )
      {
         $this->unlock();
         user_error("Invalid record number ($record_num).", E_USER_ERROR);
         return false;
      }

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();
      $offset = $index[$record_num];

      // Read the record
      list($record, $rsize) = $this->read_record($this->data_fp, $offset);

      $this->unlock();

      return $record;
   }


   /*!
    * @function getbyfield
    * @abstract retrieves records in the database whose field matches the
    * given regular expression.
    * @param fieldname  the field which to do matching on
    * @param regex  the regular expression to match a field on.
    * Note: you should include the delimiters ("/php/i" for example).
    * @param orderby  order the results.  Set to the field name to order by
    * (as a string). If left unset, sorting is not done and it is a lot faster.
    * If prefixed by "!", results will be ordered in reverse order.  
    * If orderby is an array, the 1st element refers to the field to order by,
    * and the 2nd, a function that will take two take two parameters A and B 
    * - two fields from two records - used to do the ordering.  It is expected 
    * that the function would return -ve if A < B and +ve if A > B, or zero 
    * if A == B (to order in ascending order).
    * @param includeindex  if true, an extra field called 'FFDB_IFIELD' will
    * be added to each record returned.  It will contain an int that specifies
    * the original position in the database (zero based) that the record is 
    * positioned.  It might be useful when an orderby is used, and an future 
    * operation on a record is required, given it's index in the table.
    * @result matching records in an array, or false on failure
    */
   function getbyfield(
      $fieldname, 
      $regex, 
      $orderby = NULL, 
      $includeindex = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Check the field name
      if (!$this->key_exists_array($fieldname, $this->fields))
      {
         user_error(
            "Invalid field name for getbyfield: $fieldname", 
            E_USER_ERROR
         );
         return false;
      }

      // If there are no records, return
      if ($this->records == 0)
         return array();

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();

      // Read each record and add it to an array
      $rcount = 0;
      foreach($index as $offset)
      {
         // Read the record
         list($record, $rsize) = $this->read_record($this->data_fp, $offset);

         // See if the record matches the regular expression
         if (preg_match($regex, $record[$fieldname]))
         {
            // Add the index field if required
            if ($includeindex)
               $record[FFDB_IFIELD] = $rcount;

            $result[] = $record;
         }
         
         ++$rcount;
      }

      $this->unlock();

      // Re-order as required
      if ($orderby !== NULL)
         return $this->order_by($result, $orderby);
      else
         return $result;
   }


   /*!
    * @function getbyfunction
    * @abstract retrieves all records in the database, passing each record 
    * into a given function.  If that function returns true, then it is added
    * to the result (array) list.
    * @param selectfn  the function that accepts one argument (an array record),
    * and returns true or false.
    * @param orderby  order the results.  Set to the field name to order by
    * (as a string). If left unset, sorting is not done and it is a lot faster.
    * If prefixed by "!", results will be ordered in reverse order.  
    * If orderby is an array, the 1st element refers to the field to order by,
    * and the 2nd, a function that will take two take two parameters A and B 
    * - two fields from two records - used to do the ordering.  It is expected 
    * that the function would return -ve if A < B and +ve if A > B, or zero 
    * if A == B (to order in ascending order).
    * @param includeindex  if true, an extra field called 'FFDB_IFIELD' will
    * be added to each record returned.  It will contain an int that specifies
    * the original position in the database (zero based) that the record is 
    * positioned.  It might be useful when an orderby is used, and an future 
    * operation on a record is required, given it's index in the table.
    * @result all database records as an array
    */
   function getbyfunction($selectfn, $orderby = NULL, $includeindex = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // If there are no records, return
      if ($this->records == 0)
         return array();

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();

      // Read each record and add it to an array
      $rcount = 0;
      foreach($index as $offset)
      {
         // Read the record
         list($record, $rsize) = $this->read_record($this->data_fp, $offset);

         // Add it to the result if the $selectfn OK's it
         if ($selectfn($record) == true)
         {
            // Add the index field if required
            if ($includeindex)
               $record[FFDB_IFIELD] = $rcount;

            $result[] = $record;
         }

         ++$rcount;
      }

      $this->unlock();

      // Re-order as required
      if ($orderby !== NULL)
         return $this->order_by($result, $orderby);
      else
         return $result;
   }


   /*!
    * @function getall
    * @abstract retrieves all records in the database, each record in an array
    * element.
    * @param orderby  order the results.  Set to the field name to order by
    * (as a string). If left unset, sorting is not done and it is a lot faster.
    * If prefixed by "!", results will be ordered in reverse order.  
    * If orderby is an array, the 1st element refers to the field to order by,
    * and the 2nd, a function that will take two take two parameters A and B 
    * - two fields from two records - used to do the ordering.  It is expected 
    * that the function would return -ve if A < B and +ve if A > B, or zero 
    * if A == B (to order in ascending order).
    * @param includeindex  if true, an extra field called 'FFDB_IFIELD' will
    * be added to each record returned.  It will contain an int that specifies
    * the original position in the database (zero based) that the record is 
    * positioned.  It might be useful when an orderby is used, and an future 
    * operation on a record is required, given it's index in the table.
    * @result all database records as an array
    */
   function getall($orderby = NULL, $includeindex = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // If there are no records, return
      if ($this->records == 0)
         return array();

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();

      // Read each record and add it to an array
      $rcount = 0;
      foreach($index as $offset)
      {
         // Read the record
         list($record, $rsize) = $this->read_record($this->data_fp, $offset);

         // Add the index field if required
         if ($includeindex)
            $record[FFDB_IFIELD] = $rcount++;

         // Add it to the result
         $result[] = $record;
      }

      $this->unlock();

      // Re-order as required
      if ($orderby !== NULL)
         return $this->order_by($result, $orderby);
      else
         return $result;
   }


   /*!
    * @function getkeys
    * @abstract retrieves all keys in the database, each in an array.
    * @result all database record keys as an array, in order, or false
    * if the database does not use keys.
    */
   function getkeys()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // If there is no key, return false
      if ($this->primary_key == "")
         return false;

      // If there are no records, return
      if ($this->records == 0)
         return array();

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();

      // Read each record key and add it to an array
      foreach($index as $offset)
      {
         // Read the record key and add it to the result
         $records[] = $this->read_record_key($this->data_fp, $offset);
      }

      $this->unlock();

      return $records;
   }


   /*!
    * @function exists
    * @abstract Searches the database for an item, and returns true 
    * if found, false otherwise.
    * @param key  primary key of record to search for, or the record
    * number (zero based) for databases without a primary key.
    * @result true if found, false otherwise
    */
   function exists($key)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Assume we won't find it until proven otherwise
      $result = false;

      if (!$this->lock_read())
         return false;

      // Read the index
      $index = $this->read_index();
      
      if ($this->primary_key != "")
      {
         // Do a binary search to find the item
         $pos = $this->bsearch($index, 0, $this->records-1, $key);

         if ($pos > 0)
         {
            // Found!
            $result = true;
         }
      }
      else
      {
         // Ensure the record number is an int
         if (!is_int($key))
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         // Ensure the record number is within range
         if ( ($key<0) || ($key>$this->records-1) )
         {
            $this->unlock();
            user_error("Invalid record number ($key).", E_USER_ERROR);
            return false;
         }

         // ... must be found!
         $result = true;
      }
      
      $this->unlock();

      return $result;
   }


   /*!
    * @function size
    * @abstract Returns the number of records in the database
    * @result int - the number of records in the database
    */
   function size()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }
      return $this->records;
   }


   /*!
    * @function sizedirty
    * @abstract Returns the number of dirty records in the database,
    * that would be removed if cleanup() is called.
    * @result int - the number of dirty records in the database
    */
   function sizedirty()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }
      return $this->deleted;
   }


   /*!
    * @function schema
    * @abstract Returns the current database schema in the same form
    * as that used in the parameter for the create(...) method.
    * @result array - the database schema in the format used for the 
    * create(...) method, or false if failure.
    */
   function schema()
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Reconstruct the schema array
      $result = array();
      foreach($this->fields as $key => $type)
      {
         $item = array($key, $type);

         if ($type == FFDB_INT_AUTOINC)
            array_push($item, $this->autoinc[$key]);

         if ($key == $this->primary_key)
            array_push($item, "key");

         array_push($result, $item);
      }

      return $result;
   }


   /*!
    * @function addfield
    * @abstract Adds a field to the database.  Note that this is a fairly
    * expensive operation as the database has to be rebuilt.
    * WARNING: Do not call this method unless you are sure that no other
    * people are using the database at the same time.  This will cause their
    * PHP scripts to fail.  FFDB does not check to see if the database schema
    * has been changed while in use (too much overhead).
    * @param name  name of the new field -- must not already exist
    * @param type  type of the new field (FFDB_INT, FFDB_INT_AUTOINC, 
    * FFDB_STRING, FFDB_ARRAY, FFDB_FLOAT, FFDB_BOOL)
    * @param default  default value for new field in all entries
    * @param iskey  true if the new field is to become the new primary key,
    * false otherwise.  Not that this can only be TRUE if the database
    * is empty, otherwise we will have multiple records with the same (default)
    * key, which would make the database invalid.  The primary key cannot be
    * an array or boolean type.
    * @result bool - true on success, false on failure
    */
   function addfield($name, $type, $default, $iskey = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Only allow keys if the database has no records
      if ( $iskey && ($this->records > 0) )
         return false;

      // Make sure the name of the field is a string and it is unique
      if (!is_string($name))
         return false;

      foreach ($this->fields as $key => $value)
      {
         if ($name == $key)
            return false;
      }

      // Make sure that the array or boolean value is NOT the key
      if ( $iskey && ( ($type == FFDB_ARRAY) || ($type == FFDB_BOOL) ) )
         return false;

      // Make sure the field type is one of our constants
      if (!is_int($type))
         return false;

      switch($type)
      {
         case FFDB_INT_AUTOINC:
            // We use the default value as the starting value of the auto-inc
            if (!is_int($default) && ($this->records > 0) )
            {
               user_error(
                  "Invalid starting value for autoinc int.", 
                  E_USER_ERROR
               );
               return false;
            }
            break;

         case FFDB_INT:
            if (!is_int($default) && ($this->records > 0) )
            {
               user_error("Invalid default value for int.", E_USER_ERROR);
               return false;
            }
            break;

         case FFDB_STRING:
            if (!is_string($default) && ($this->records > 0) )
            {
               user_error("Invalid default value for string.", E_USER_ERROR);
               return false;
            }
            break;

         case FFDB_ARRAY:
            if (!is_array($default) && ($this->records > 0) )
            {
               user_error("Invalid default value for array", E_USER_ERROR);
               return false;
            }
            break;

         case FFDB_FLOAT:
            if (!is_float($default) && ($this->records > 0) )
            {
               user_error("Invalid default value for float", E_USER_ERROR);
               return false;
            }
            break;

         case FFDB_BOOL:
            if (!is_bool($default) && ($this->records > 0) )
            {
               user_error("Invalid default value for bool", E_USER_ERROR);
               return false;
            }
            break;
         
         default:
            // Unknown type...!
            user_error("Invalid type in field (found $type)", E_USER_ERROR);
            return false;
      }

      if (!$this->lock_write())
         return false;

      // Note that we attempt the file creation under the DB lock, so
      // that another process doesn't try to create the same file at the
      // same time.
      $tmpdb = @fopen($this->dbname.".tmp", "wb+");
      if (!$tmpdb)
      {
         $this->unlock();
         return false;
      }

      // Add the field to the schema
      $this->fields[$name] = $type;

      if ($type == FFDB_INT_AUTOINC)
         $this->autoinc[$name] = $default;

      // Do we have a new primary key?
      if ($iskey)
         $this->primary_key = $name;

      // Read in the current index
      $index = $this->read_index();

      // Now translate the data file.  For each index entry, read in
      // the record, add in the new default value, then write it back
      // out to a new temporary file.  Then move that temporary file
      // back over the old data file.

      // For each item in the index, move it from the current database
      // file to the new one.  Also update the new file offset in the index
      // so we can write it back out to the index file.
      for($i=0; $i<$this->records; ++$i)
      {
         $offset = $index[$i];

         // Read in the entire record
         unset($record);
         list($record, $rsize) = $this->read_record($this->data_fp, $offset);

         // Save the new file offset
         $index[$i] = ftell($tmpdb);

         // Add in the new field to the record
         if ($type == FFDB_INT_AUTOINC)
            $record[$name] = $this->autoinc[$name]++;
         else
            $record[$name] = $default;

         // Write out the record to the temporary file
         if (!$this->write_record($tmpdb, $record)) 
         {
            // Error writing item to the database
            fclose($tmpdb);
            @unlink($this->dbname.".tmp");
            $this->unlock();
            return false;
         }
      }

      // Move the temporary file over the original database file.
      fclose($tmpdb);
      fclose($this->data_fp);
      @unlink($this->dbname.".dat");
      @rename($this->dbname.".tmp", $this->dbname.".dat");

      // Since we've effectively done a cleanup(), set the number 
      // of (unclean) deleted items to zero.
      $this->deleted = 0;

      // Write the new schema to the meta data file
      $this->write_schema();

      // Write out the index (which may have been overwritten)
      $this->write_index($index);

      // Re-open the database data file
      $this->data_fp = @fopen($this->dbname.".dat", "rb+");
      if (!$this->data_fp) 
      {
         $this->unlock();
         return false;
      }

      $this->unlock();

      return true;
   }


   /*!
    * @function removefield
    * @abstract Removes a field from the database.  Note that this is a fairly
    * expensive operation as the database has to be rebuilt.
    * WARNING: Do not call this method unless you are sure that no other
    * people are using the database at the same time.  This will cause their
    * PHP scripts to fail.  FFDB does not check to see if the database schema
    * has been changed while in use (too much overhead).
    * @param fieldname  name of the field to delete -- must currently exist
    * @result bool - true on success, false on failure
    */
   function removefield($fieldname)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // Make sure the name of the field is a string and it exists
      if (!is_string($fieldname))
         return false;

      if (!$this->key_exists_array($fieldname, $this->fields))
         return false;

      if (!$this->lock_write())
         return false;

      // Note that we attempt the file creation under the DB lock, so
      // that another process doesn't try to create the same file at the
      // same time.
      $tmpdb = @fopen($this->dbname.".tmp", "wb+");
      if (!$tmpdb)
      {
         $this->unlock();
         return false;
      }

      // Save a copy of the field list.  It is needed in the main
      // loop to play with the fields[] array and {read, write}_record(...)
      $oldfields = $this->fields;

      // Read in the current index
      $index = $this->read_index();

      // Now translate the data file.  For each index entry, read in
      // the record, remove the deleted field, then write it back
      // out to a new temporary file.  Then move that temporary file
      // back over the old data file.

      // For each item in the index, move it from the current database
      // file to the new one.  Also update the new file offset in the index
      // so we can write it back out to the index file.
      if (count($this->fields) > 1)
      {
         for($i=0; $i<$this->records; ++$i)
         {
            // Use the original fields[] array so read_record(...) will 
            // operate correctly for the old-format database .dat file.
            $this->fields = $oldfields;

            // Read in the entire record
            unset($record);
            list($record, $rsize) 
               = $this->read_record($this->data_fp, $index[$i]);
            if ($record === false)
            {
                  // Error reading item from the database
                  fclose($tmpdb);
                  @unlink($this->$dbname.".tmp");
                  $this->unlock();
                  return false;
            }

            // Save the new file offset
            $index[$i] = ftell($tmpdb);

            // Remove the field from the record
            unset($record[$fieldname]);

            // Make sure the field[] array DOES NOT include the original field,
            // so write_record(...) will operate correctly and write out a 
            // new-stlye database .dat file (without the deleted field).
            unset($this->fields[$fieldname]);

            // Write out the record to the temporary file
            if (!$this->write_record($tmpdb, $record)) 
            {
               // Error writing item to the database
               fclose($tmpdb);
               @unlink($this->dbname.".tmp");
               $this->unlock();
               return false;
            }
         }
      }
      else
      {
         // We have deleted the last field, so 
         // there are essentially no records left.
         $this->records = 0;
      }
      
      // Remove the field to the schema
      unset($this->fields[$fieldname]);
      if (isset($this->autoinc[$fieldname]))
         unset($this->autoinc[$fieldname]);

      // Is the field to be removed the primary key?
      if ($this->primary_key == $fieldname)
         $this->primary_key = "";

      // Move the temporary file over the original database file.
      fclose($tmpdb);
      fclose($this->data_fp);
      @unlink($this->dbname.".dat");
      @rename($this->dbname.".tmp", $this->dbname.".dat");

      // Since we've effectively done a cleanup(), set the number 
      // of (unclean) deleted items to zero.
      $this->deleted = 0;

      // Write the new schema to the meta data file
      $this->write_schema();

      // Write out the index
      $this->write_index($index);

      // Re-open the database data file
      $this->data_fp = @fopen($this->dbname.".dat", "rb+");
      if (!$this->data_fp) 
      {
         $this->unlock();
         return false;
      }

      $this->unlock();

      return true;
   }


   /*!
    * @function lock_write
    * @abstract lock the database for a write
    * @param force  force the lock to stick until unlocked by force
    */
   function lock_write($force = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      // If the DB is locked (for writing), don't bother locking again
      if ( ($this->locked == 2) || ($this->forcelock == 2) )
         return true;

      if ($force)
         $this->forcelock = 2;

      // Lock the index file
      $this->locked = 2;

      if (!flock($this->meta_fp, $this->locked))
      {
         user_error(
            "Could not (write) lock database "
            ."'".$this->dbname."'",
            E_USER_ERROR
         );
         return false;
      }

      return true;
   }


   /*!
    * @function lock_read
    * @abstract lock the database for a read
    * @param force force the lock to stick until unlocked by force
    */
   function lock_read($force = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }
      
      // If the DB is locked (for reading), don't bother locking again
      if ( ($this->locked == 1) || ($this->forcelock == 1) )
         return true;

      if ($force)
         $this->forcelock = 1;

      // Lock the index file
      $this->locked = 1;

      if (!flock($this->meta_fp, $this->locked))
      {
         user_error(
            "Could not (read) lock database "
            ."'".$this->dbname."'",
            E_USER_ERROR
         );
         return false;
      }

      return true;
   }


   /*!
    * @function unlock
    * @abstract unlock the database
    * @param force unlock a previously forced (sticky) lock
    */
   function unlock($force = false)
   {
      if (!$this->isopen)
      {
         user_error("Database not open.", E_USER_ERROR);
         return false;
      }

      if ($force)
         $this->forcelock = 0;
      
      // If the DB is unlocked, don't bother unlocking again
      if ( ($this->locked == 0) || ($this->forcelock != 0) )
         return true;

      // Unlock the index file
      $this->locked = 0;

      if (!flock($this->meta_fp, 3))
      {
         user_error(
            "Could not unlock database "
            ."'".$this->dbname."'",
            E_USER_ERROR
         );
         return false;
      }

      return true;
   }


   /*!
    * @function order_by
    * @abstract private function to sort a result set by a particular field.
    * @param result  the result list to order
    * @param orderby  order the results.  Set to the field name to order by
    * (as a string). If left unset, sorting is not done and it is a lot faster.
    * If prefixed by "!", results will be ordered in reverse order.  
    * If orderby is an array, the 1st element refers to the field to order by,
    * and the 2nd, a function that will take two take two parameters A and B 
    * - two fields from two records - used to do the ordering.  It is expected 
    * that the function would return -ve if A < B and +ve if A > B, or zero 
    * if A == B (to order in ascending order).
    * @result array - input results ordered as required, or false on error.
    */
   function order_by(&$result, $orderby)
   {
      $record_count = count($result);

      // Do we want reverse sort?
      $rev_sort 
         = is_string($orderby) && 
           (strlen($orderby) > 0) && 
           ($orderby[0] == "!");

      // Do we want to use a function?
      $use_funct = (is_array($orderby));

      // Remove the control code(s) from the order by field
      if ($rev_sort)
         $orderby = substr($orderby, 1);

      if ($use_funct)
      {
         $funct = $orderby[1];
         $orderby = $orderby[0];
      }

      // Check the order by field name
      if (!$this->key_exists_array($orderby, $this->fields))
      {
         user_error(
            "Invalid orderby field name ($orderby).", 
            E_USER_ERROR
         );
         return false;
      }
      if ($this->fields[$orderby] == FFDB_ARRAY)
      {
         user_error(
            "Cannot orderby on an array field ($orderby).", 
            E_USER_ERROR
         );
         return false;
      }
      if ($use_funct && !function_exists($funct))
      {
         user_error(
            "Invalid orderby user function ($funct).", 
            E_USER_ERROR
         );
         return false;
      }

      // Construct an array that points into our list
      // We use an array of indices into $result, because there might
      // be more than one record with a given sortby-field.
      $sorted = array();
      for($i=0; $i<$record_count; ++$i)
      {
         $key = &$result[$i][$orderby];

         $sorted[$key][] = $i;
      }

      // Sort the array

      if ($rev_sort)
         krsort($sorted);           // Reverse (decending) sort
      else if ($use_funct)
         uksort($sorted, $funct);   // User function sort
      else
         ksort($sorted);            // Regular (ascending) sort

      // Rearrange the items to form the result.  Unfortunately
      // because we will return the array, we can't use references,
      // and we end up having to copy all records across to a new array.
      foreach($sorted as $ilist)
         foreach($ilist as $index)
            $sresult[] = $result[$index];

      return $sresult;
   }


   /*!
    * @function automatic_cleanup
    * @abstract private function to clean up the database if the number of
    * dirty records have exceeded a threshold value.
    */
   function automatic_cleanup()
   {
      if ($this->deleted > $this->auto_clean)
         $this->cleanup();
   }

   
   /*!
    * @function read_schema
    * @abstract private function to read the database schema and other meta
    * information.  We assume the database has been locked before calling 
    * this function.
    */
   function read_schema()
   {
      fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

      // Read the database statistics from the meta file.
      //
      // Statistics format:
      //    [number of valid records: int]
      //    [number of (unclean) deleted records: int]

      $this->records = $this->read_int($this->meta_fp);
      $this->deleted = $this->read_int($this->meta_fp);

      // Read the schema from the meta file.
      //
      // Schema format:
      //   [primary key field name]
      //   [number of fields]
      //     [field 1: name]
      //     [field 1: type]
      //     ...
      //     [field n: name]
      //     [field n: type]
      //
      // For auto-incrementing fields, there is an extra int specifying
      // the last value used in the last record added.
      
      $this->primary_key = $this->read_str($this->meta_fp);
      $field_count = $this->read_int($this->meta_fp);

      $this->fields = array();
      $this->autoinc = array();
      for ($i=0; $i<$field_count; ++$i)
      {
         // Read the fields in
         $name = $this->read_str($this->meta_fp);
         $type = $this->read_byte($this->meta_fp);
         $this->fields[$name] = $type;

         if ($type == FFDB_INT_AUTOINC)
            $this->autoinc[$name] = $this->read_int($this->meta_fp);
      }
      
      if ($field_count == 0)
      {
         $this->fields = array();
         $this->autoinc = array();
      }

      // Save where the index starts in the meta file
      $this->index_start = ftell($this->meta_fp);      
   }


   /*!
    * @function write_schema
    * @abstract private function to write the database schema and other meta
    * information.  We assume the database has been locked before calling 
    * this function.
    */
   function write_schema()
   {
      fseek($this->meta_fp, FFDB_INDEX_RECORDS_OFFSET, SEEK_SET);

      // Write the database statistics information
      //
      // Statistics format:
      //    [number of valid records: int]
      //    [number of (unclean) deleted records: int]

      $this->write_int($this->meta_fp, $this->records);
      $this->write_int($this->meta_fp, $this->deleted);

      // Write the schema from the meta file.
      //
      // Schema format:
      //   [primary key field name]
      //   [number of fields]
      //     [field 1: name]
      //     [field 1: type]
      //     ...
      //     [field n: name]
      //     [field n: type]
      //
      // For auto-incrementing fields, there is an extra int specifying
      // the last value used in the last record added.
      
      $this->write_str($this->meta_fp, $this->primary_key);
      $this->write_int($this->meta_fp, count($this->fields));

      // Write the key entry first, always
      if ($this->primary_key != "")
      {
         $this->write_str($this->meta_fp, $this->primary_key); 
         $this->write_byte(
            $this->meta_fp, 
            $this->fields[$this->primary_key]
         );

         if ($this->fields[$this->primary_key] == FFDB_INT_AUTOINC)
            $this->write_int(
               $this->meta_fp, 
               $this->autoinc[$this->primary_key]
            );
      }

      // Write out all of the other entries
      foreach ($this->fields as $name => $type)
      {
         if ($name != $this->primary_key)
         {
            $this->write_str($this->meta_fp, $name);
            $this->write_byte($this->meta_fp, $type);

            if ($type == FFDB_INT_AUTOINC)
               $this->write_int($this->meta_fp, $this->autoinc[$name]);   
         }
      }

      $this->index_start = ftell($this->meta_fp);
   }


   /*!
    * @function read_index
    * @abstract private function to return the index values.  We assume the
    * database has been locked before calling this function.
    * @result array - list of file offsets into the .dat file
    */
   function read_index()
   {
      fseek($this->meta_fp, $this->index_start, SEEK_SET);

      // Read in the index
      $index = array();
      for($i=0; $i<$this->records; ++$i)
         $index[] = $this->read_int($this->meta_fp);

      return $index;
   }


   /*!
    * @function write_index
    * @abstract private function to write the index values.  We assume the
    * database has been locked before calling this function.
    * @param index  the index *data* to write out
    */
   function write_index(&$index)
   {
      fseek($this->meta_fp, $this->index_start, SEEK_SET);
      ftruncate($this->meta_fp, $this->index_start);

      for($i=0; $i<$this->records; ++$i)
         $this->write_int($this->meta_fp, $index[$i]);
   }


   /*!
    * @function read_record
    * @abstract Private function to read a record from the database
    * @param fp  the file pointer used to read a record from
    * @param offset  file offset into the .dat file
    * @result Returns false on error, or the record otherwise
    */
   function read_record($fp, $offset)
   {
      // Read in the record at the given offset.
      fseek($fp, $offset, SEEK_SET);

      // Read in the size of the block allocated for the record
      $size = $this->read_int($fp);

      // Read in the entire record
      foreach($this->fields as $item => $datatype)
         $record[$item] = $this->read_item($fp, $datatype);
      
      return array($record, $size);
   }


   /*!
    * @function read_record_key
    * @abstract Private function to read a record KEY from the database.  Note
    * that this function relies on the fact that they key is ALWAYS the first
    * item in the database record as stored on disk.
    * @param fp  the file pointer used to read a record from
    * @param offset  file offset into the .dat file
    * @result Returns false on error, or the key otherwise
    */
   function read_record_key($fp, $offset)
   {
      // Read in the record at the given offset.
      fseek($fp, $offset+FFDB_INDEX_RBLOCK_SIZE, SEEK_SET);

      // Read in the record KEY only
      return $this->read_item($fp, $this->fields[$this->primary_key]);
   }


   /*!
    * @function write_record
    * @abstract Private function to write a record to the END of the .dat file
    * @param fp  the file pointer used to write a record to
    * @param record  the record to write
    * @param size  the size of the record.  
    * @param atoffset  the offset to write to, or -1 for the current position
    * @result Returns false on error, true otherwise
    */
   function write_record($fp, &$record, $size = -1)
   {
      // Auto-calculate the record size
      if ($size < 0)
         $size = $this->record_size($record);

      // Write out the size of the record
      $this->write_int($fp, $size);

      // Write out the entire record
      foreach($this->fields as $item => $datatype)
      {
         if (!$this->write_item($fp, $datatype, $record[$item]))
            return false;
      }
      
      return true;
   }


   /*!
    * @function record_size
    * @abstract Private function to determine the size (bytes) of a record
    * @param record  the record to investigate
    * @result int - the size of the record.
    */
   function record_size(&$record)
   {
      $size = 0;

      // Size up each field
      foreach($this->fields as $item => $datatype)
         $size += $this->item_size($datatype, $record[$item]);
      
      return $size;
   }


   /*!
    * @function bsearch
    * @abstract Private function to perform a binary search
    * @param index  file offsets into the .dat file, it must be ordered 
    * by primary key.
    * @param left  the left most index to start searching from
    * @param right  the right most index to start searching from
    * @param target  the search target we're looking for
    * @result Returns -[insert pos+1] when not found, or the array index+1 
    * when found. Note that we don't return the normal position, because we 
    * can't differentiate between -0 and +0.
    */
   function bsearch(&$index, $left, $right, &$target)
   {
      while ($left <= $right)
      {
         $middle = (int)(($left+$right)/2);
        
         // Read in the record key at the given offset
         $key = $this->read_record_key($this->data_fp, $index[$middle]);

         if ( ($left == $right) && ($key != $target) )
         {
            if ($target < $key)
               return -($left+1);
            else
               return -($left+1+1);
         }
         else if ($key == $target)
         {
            // Found!
            return $middle+1;
         }
         else if ($target < $key)
         {
            // Try the left side
            $right = $middle-1;
         }
         else /* $target > $key */
         {
            // Try the right side
            $left = $middle+1;
         }
      }

      // Not found: return the insert position (as negative)
      return -($middle+1);
   }


   /*!
    * @function read_int
    * @abstract Reads an int (4 bytes) from a file
    * @param fp  file pointer - pointer to an open file
    * @result the read int
    */
   function read_int($fp)
   {
      return $this->bin2dec(fread($fp, 4), 4);
   }


   /*!
    * @function read_byte
    * @abstract Reads a byte from a file
    * @param fp  file pointer - pointer to an open file
    * @result the read byte as an int
    */
   function read_byte($fp)
   {
      return $this->bin2dec(fread($fp, 1), 1);
   }


   /*!
    * @function read_float
    * @abstract Reads a float (6 bytes) from a file
    * @param fp  file pointer - pointer to an open file
    * @result the read float
    */
   function read_float($fp)
   {
      return $this->bin2float(fread($fp, 6));
   }


   /*!
    * @function read_str
    * @abstract Reads a string from a file
    * @param fp  file pointer - pointer to an open file
    * @result the read string
    */
   function read_str($fp)
   {
      $strlen = $this->bin2dec(fread($fp, 4), 4);
      return fread($fp, $strlen);
   }


   /*!
    * @function write_int
    * @abstract Writes an int (4 bytes) to a file
    * @param fp  file pointer - pointer to an open file
    * @param num  int - the int to write
    */
   function write_int($fp, $num)
   {
      fwrite($fp, $this->dec2bin($num, 4), 4);
   }


   /*!
    * @function write_byte
    * @abstract Writes a byte to a file
    * @param fp  file pointer - pointer to an open file
    * @param num  int - the byte to write
    */
   function write_byte($fp, $num)
   {
      fwrite($fp, $this->dec2bin($num&0xFF, 1), 1);
   }


   /*!
    * @function write_float
    * @abstract Writes a float (6 bytes) to a file
    * @param fp  file pointer - pointer to an open file
    * @param num  float - the float to write
    */
   function write_float($fp, $num)
   {
      fwrite($fp, $this->float2bin($num), 6);
   }


   /*!
    * @function write_str
    * @abstract Writes a string to a file
    * @param fp  file pointer - pointer to an open file
    * @param str  string - the string to write
    */
   function write_str($fp, &$str)
   {
      $len = strlen($str);
      fwrite($fp, $this->dec2bin($len, 4), 4);
      fwrite($fp, $str, $len);
   }


   /*!
    * @function dec2bin
    * @abstract Convers an int to a binary string, low byte first
    * @param num  int - number to convert
    * @param bytes  int - minimum number of bytes to covert to
    * @result the binary string form of the number
    */
   function dec2bin($num, $bytes)
   {
      $result = "";
      for($i=0; $i<$bytes; ++$i)
      {
         $result .= chr($num&0xFF);
         $num = $num >> 8;
      }

      return $result;
   }


   /*!
    * @function bin2dec
    * @abstract Converts a binary string to an int, low byte first
    * @param str  string - binary string to convert
    * @param len  int - length of the binary string to convert
    * @result the int version of the binary string
    */
   function bin2dec(&$str, $len)
   {
      $shift = 0;
      $result = 0;
      for($i=0; $i<$len; ++$i)
      {
         $result |= (@ord($str[$i])<<$shift);
         $shift += 8;
      }

      return $result;
   }


   /*!
    * @function float2bin
    * @abstract Converts a single-precision floating point number
    * to a 6 byte binary string.
    * @param num  float - the float to convert
    * @result the binary string representing the float
    */
   function float2bin($num)
   {
      // Save the sign bit
      $sign = ($num<0)?0x8000:0x0000;

      // Now treat the number as positive...
      if ($num<0) $num = -$num;

      // Get the exponent and limit to 15 bits
      $exponent = (1+(int)floor(log10($num)))&0x7FFF;

      // Convert the number into a fraction
      $num /= pow(10, $exponent);

      // Now convert the fraction to a 31bit int.
      // We don't use the full 32bits, because the -ve numbers
      // stuff us up -- this results in better than single
      // precision floats, but not as good as double precision.
      $fraction = (int)floor($num*0x7FFFFFFF);

      // Pack the number into a 6 byte binary string
      return 
         $this->dec2bin($sign | $exponent, 2) 
        .$this->dec2bin($fraction, 4);
   }


   /*!
    * @function bin2float
    * @abstract Converts a 6 byte binary string to a single-precision
    * floating point number
    * @param data  string - the binary string to convert
    * @result the floating point number
    */
   function bin2float(&$data)
   {
      // Extract the sign bit and exponent
      $exponent = $this->bin2dec(substr($data, 0, 2), 2);
      $sign = (($exponent&0x8000) == 0)?1:-1;
      $exponent &= 0x7FFF;

      // Extract the fractional part
      $fraction = $this->bin2dec(substr($data, 2, 4), 4);

      // return the reconstructed float
      return $sign*pow(10, $exponent)*$fraction/0x7FFFFFFF;
   }



   /*!
    * @function write_item
    * @abstract Writes out a data type to a file.  Note that arrays can only 
    * consist of other arrays, ints, and strings.
    * @param fp  file pointer - pointer to data file
    * @param type  data type to write
    * @param data  actual data to write
    * @result bool - true on success, false on failure
    */
   function write_item($fp, $type, &$data)
   {
      switch ($type)
      {
         case FFDB_INT:
         case FFDB_INT_AUTOINC:
            $this->write_int($fp, $data);
            break;

         case FFDB_STRING:
            $this->write_str($fp, $data);
            break;

         case FFDB_FLOAT:
            $this->write_float($fp, $data);
            break;

         case FFDB_BOOL:
            $this->write_byte($fp, ($data == true)?1:0);
            break;

         case FFDB_ARRAY:
            $this->write_int($fp, count($data));
            foreach($data as $k => $d)
            {
               // Write the array key
               if (is_int($k))
               {
                  $this->write_byte($fp, FFDB_INT);
                  $this->write_int($fp, $k);
               }
               else if (is_string($k))
               {
                  $this->write_byte($fp, FFDB_STRING);
                  $this->write_str($fp, $k);
               }
               else if (is_float($k))
               {
                  $this->write_byte($fp, FFDB_FLOAT);
                  $this->write_float($fp, $k);
               }
               else if (is_bool($k))
               {
                  $this->write_byte($fp, FFDB_BOOL);
                  $this->write_byte($fp, ($k == true)?1:0);
               }
               else
               {
                  // Error in type
                  user_error("Invalid array key data type ($k)", E_USER_ERROR);
                  return false;
               }

               // Write the array data
               if (is_int($d))
               {
                  $this->write_byte($fp, FFDB_INT);
                  $this->write_int($fp, $d);
               }
               else if (is_string($d))
               {
                  $this->write_byte($fp, FFDB_STRING);
                  $this->write_str($fp, $d);
               }
               else if (is_float($d))
               {
                  $this->write_byte($fp, FFDB_FLOAT);
                  $this->write_float($fp, $d);
               }
               else if (is_bool($d))
               {
                  $this->write_byte($fp, FFDB_BOOL);
                  $this->write_byte($fp, ($d == true)?1:0);
               }
               else if (is_array($d))
               {
                  $this->write_byte($fp, FFDB_ARRAY);
                  $this->write_item($fp, FFDB_ARRAY, $d);
               }
               else
               {
                  // Error in type
                  user_error("Invalid array data type ($d)", E_USER_ERROR);
                  return false;
               }
            }
            break;
         
         default:
            // Error in type
            user_error("Invalid data type ($type)", E_USER_ERROR);
            return false;
      }

      return true;
   }


   /*!
    * @function read_item
    * @abstract Reads a data type from a file.  Note that arrays can only 
    * consist of other arrays, ints, and strings.
    * @param fp  file pointer - pointer to data file
    * @param type  data type to read
    * @result bool - data on success, false on failure
    */
   function read_item($fp, $type)
   {
      switch ($type)
      {
         case FFDB_INT:
         case FFDB_INT_AUTOINC:
            return $this->read_int($fp);

         case FFDB_STRING:
            return $this->read_str($fp);

         case FFDB_FLOAT:
            return $this->read_float($fp);

         case FFDB_BOOL:
            return ($this->read_byte($fp) == 1);

         case FFDB_ARRAY:
            $elements = $this->read_int($fp);
            for ($i=0; $i<$elements; ++$i)
            {
               // Get the array key data type
               $keytype = $this->read_byte($fp);

               switch($keytype)
               {
                  case FFDB_INT:
                     $key = $this->read_int($fp);
                     break;
                  case FFDB_STRING:
                     $key = $this->read_str($fp);
                     break;
                  case FFDB_FLOAT:
                     $key = $this->read_float($fp);
                     break;
                  case FFDB_BOOL:
                     $key = ($this->read_byte($fp) == 1);
                     break;
               }

               // Get the array data type
               $datatype = $this->read_byte($fp);

               switch($datatype)
               {
                  case FFDB_INT:
                     $data = $this->read_int($fp);
                     break;
                  case FFDB_STRING:
                     $data = $this->read_str($fp);
                     break;
                  case FFDB_FLOAT:
                     $data = $this->read_float($fp);
                     break;
                  case FFDB_BOOL:
                     $data = ($this->read_byte($fp) == 1);
                     break;
                  case FFDB_ARRAY:
                     $data = $this->read_item($fp, FFDB_ARRAY);
                     break;
               }

               $result[$key] = $data;
            }
            
            // Preserve NULL arrays...
            if (!isset($result))
               $result = array();

            return $result;

         default:
            // Error in type
            user_error("Invalid data type ($type)", E_USER_ERROR);
            return false;
      }

      // Error
      return false;
   }


   /*!
    * @function item_size
    * @abstract Returns the size of an item
    * @param type  data type
    * @param data  actual data to size
    * @result int - size in bytes
    */
   function item_size($type, &$data)
   {
      switch ($type)
      {
         case FFDB_INT:
         case FFDB_INT_AUTOINC:
            return 4;
         case FFDB_STRING:
            return 4+strlen($data);
         case FFDB_FLOAT:
            return 6;
         case FFDB_BOOL:
            return 1;
         case FFDB_ARRAY:
            $size = 0;
            foreach($data as $k => $d)
            {
               $size += 1;
               if (is_int($k))
                  $size += 4;
               else if (is_string($k))
                  $size += 4+strlen($k);
               else if (is_float($k))
                  $size += 6;
               else if (is_bool($k))
                  $size += 1;
               else
               {
                  // Error in type
                  user_error("Invalid array key data type ($k)", E_USER_ERROR);
                  return false;
               }

               $size += 1;
               if (is_int($d))
                  $size += 4;
               else if (is_string($d))
                  $size += 4+strlen($d);
               else if (is_float($d))
                  $size += 6;
               else if (is_bool($d))
                  $size += 1;
               else if (is_array($d))
                  $size += item_size(FFDB_ARRAY, $d);
               else
               {
                  // Error in type
                  user_error("Invalid array data type ($d)", E_USER_ERROR);
                  return false;
               }
            }
            return $size;
      }         

      // Error in type
      user_error("Invalid data type ($type)", E_USER_ERROR);
      return false;
   }


   /*!
    * @function key_exists_array
    * @abstract Tests to see if a key exists in a given array.  This function
    * is defined to maintain some compatibility between various versions of PHP
    * @param key  - the key to search for
    * @param array  - the array in which to search
    * @result bool - true if the key exists; false otherwise
    */
   function key_exists_array(&$key, &$array)
   {
      if (function_exists("array_key_exists"))
         return array_key_exists($key, $array);

      return in_array($key, array_keys($array));
   }

}

?>