<?php
/**
 * Form.php
 *
 * The Form class is meant to simplify the task of keeping
 * track of errors in user submitted forms and the form
 * field values that were entered correctly.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 19, 2004
 */
class Form  {
	static $values = array();  //Holds submitted form field values
	static $errors = array();  //Holds submitted form error messages
	static $num_errors;   //The number of errors in submitted form

	/**
	 * Form constructor
	 *
	 */
	static function _Form(){
		/**
		 * Get form value and error arrays, used when there
		 * is an error with a user-submitted form.
		 */
		if(isset($_SESSION['value_array']) && isset($_SESSION['error_array'])){
			self::$values = $_SESSION['value_array'];
			self::$errors = $_SESSION['error_array'];
			self::$num_errors = count(self::$errors);

			unset($_SESSION['value_array']);
			unset($_SESSION['error_array']);
		}
		else{
			self::$num_errors = 0;
		}
	}

	/**
	 * setValue - Records the value typed into the given
	 * form field by the user.
	 */
	static function setValue($field, $value){
		self::$values[$field] = $value;
	}

	/**
	 * setError - Records new form error given the form
	 * field name and the error message attached to it.
	 */
	static function setError($field, $errmsg){
		self::$errors[$field] = $errmsg;
		self::$num_errors = count(self::$errors);
	}

	/**
	 * value - Returns the value attached to the given
	 * field, if none exists, the empty string is returned.
	 */
	static function value($field){
		if(array_key_exists($field,self::$values)){
			return htmlspecialchars(stripslashes(self::$values[$field]));
		}else{
			return "";
		}
	}

	/**
	 * error - Returns the error message attached to the
	 * given field, if none exists, the empty string is returned.
	 */
	static function error($field){
		if(array_key_exists($field,self::$errors)){
			return "<font size=\"2\" color=\"#ff0000\">".self::$errors[$field]."</font>";
		}else{
			return "";
		}
	}

	/* getErrorArray - Returns the array of error messages */
	static function getErrorArray(){
		return self::$errors;
	}
}
?>