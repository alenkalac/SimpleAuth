<?php 
	namespace xample;

	use Silex\Application;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	class auth {

		const MIN_USERNAME = 4;
		const MIN_PASSWORD = 4;

		public function login(Request $r, Application $app) {
			$user = $r->get('username'); //5 chars at least
			$pass = $r->get('password'); //5 char or more
			$hwid = $r->get('hwid'); //32

			$output = ["error" => "", "message" => ""];

			//AUTH USER WITH USER+PASS
			//GET USER_ID
			$user_id = $this->validateUser($user, $pass, $app['db']);
			if($user_id == 0) {
				$output['error'] = "Invalid user information";
				return new Response(json_encode($output));
			}

			//GET HWID_ID 
			$hwid_id = $this->getHWID_ID($hwid, $user_id, $app['db']);
			if($hwid_id == 0) {
				$output['error'] = "Invalid HWID information";
				return new Response(json_encode($output));
			}
			
			//FIND SERIAL KEY WITH USER_ID+HWID_ID
			$valid = $this->findSerialWithUserIDandHWID($user_id, $hwid_id, $app['db']);
			if(!$valid) {
				$output['error'] = "no serial key found";
				return new Response(json_encode($output));
			} 

			$output['message'] = "success";
			return new Response(json_encode($output));

		}

		private function findSerialWithUserIDandHWID($userid, $hwid, $db) {
			//echo $userid . " " . $hwid;
			$q = $db->prepare("SELECT * FROM serial WHERE user = :USERID AND hwid = :HWID");
			$q->execute([
				"HWID" => $hwid,
				"USERID" => $userid,
			]);
			$result = $q->fetch(\PDO::FETCH_ASSOC);
			
			return $q->rowCount();
		}

		private function getHWID_ID($hwid, $userid, $db) {
			$q = $db->prepare("SELECT * FROM hwid WHERE value = :HWID AND userid = :USER");
			$q->execute([
				"HWID" => $hwid,
				"USER" => $userid
			]);
			$result = $q->fetch(\PDO::FETCH_ASSOC);
			if($q->rowCount() > 0)
				return $result['id'];
			else return false;

		}

		private function validateUser($username, $password, $db) {
			$q = $db->prepare("SELECT * FROM users WHERE username = :USER");
			$q->execute([
				"USER" => $username,
			]);
			$result = $q->fetch(\PDO::FETCH_ASSOC);

			$hashed_pass = $result['password'];

			if(password_verify($password, $hashed_pass))
				return $result['id'];
			else
				return false;
		}

		private function usernameExists($username, $db) {
			$query = $db->prepare("SELECT * FROM users WHERE username = :USER");
			$query->execute([
				"USER" => $username
			]);

			return $query->rowCount() > 0 ? true : false;
		}

		private function validSerialKey($serial, $db) {
			$query = $db->prepare("SELECT * FROM serial WHERE serial_key = :SER");
			$query->execute([
				"SER" => $serial,
			]);

			$result = $query->fetch(\PDO::FETCH_ASSOC);

			if($result['user'] == "-1") 
				return true;
			return false;
		}

		private function insertUser($username, $password, $db) {
			$query = $db->prepare("INSERT INTO users VALUES(NULL, :USER, :PASS, 'TEST')");
			$query->execute([
				"USER" => $username,
				"PASS" => password_hash($password, PASSWORD_DEFAULT)
			]);

			return $db->lastInsertId();
		}

		public function insertHWID($hwid, $userid, $db) {
			$query = $db->prepare("INSERT INTO hwid VALUES(NULL, :HWID, :USERID)");
			$query->execute([
				"HWID" => $hwid,
				"USERID" => $userid
			]);

			return $db->lastInsertId();
		}

		public function updateSerial($serial, $userid, $hwidid, $db) {
			$query = $db->prepare("UPDATE `serial` SET user = :USERID, hwid = :HWIDID WHERE serial_key = :SER");
			$query->execute([
				"HWIDID" => $hwidid,
				"USERID" => $userid,
				"SER" => $serial
			]);

			if(!$query->rowCount()) 
				throw new \PDOException();
		}

		public function register(Request $r, Application $app) {
			$user = $r->get('username'); //5 chars at least
			$pass = $r->get('password'); //5 char or more
			$serial = $r->get('serial'); // 16
			$hwid = $r->get('hwid'); //32

			$output = ["error" => "", "message" => ""];

			//MAKE SURE NO USERNAME DUPLICATES

			if(strlen($user) < self::MIN_USERNAME) {
				$output['error'] = "Username has to be more than " . self::MIN_USERNAME . " characters";
				return new Response(json_encode($output));
			} 
			if(strlen($pass) < self::MIN_PASSWORD) {
				$output['error'] = "Password has to be more than " . self::MIN_PASSWORD . " characters";
				return new Response(json_encode($output));
			} 
			
			if($this->usernameExists($user, $app['db'])) 
				$output['error'] = "user already exists";
			else if(!$this->validSerialKey($serial, $app['db']))
				$output['error'] = "Invalid Key";

			if(empty($output['error'])) {

				$app['db']->beginTransaction(); //if anything fails - rollback

				try {
					$user_id = $this->insertUser($user, $pass, $app['db']);
					$hwid_id = $this->insertHWID($hwid, $user_id, $app['db']);
					$this->updateSerial($serial, $user_id, $hwid_id, $app['db']);
					$app['db']->commit(); //save changes
					$output['message'] = "success";

				}catch(\PDOException $e) {
					$output['error'] = "Something went wrong, try again";
					$app['db']->rollback();
				}
			}

			echo json_encode($output);

			die();
		}
	}
?>