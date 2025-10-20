<?php

function register_user(string $fullname, string $email, string $password): bool
{
    try {
        $pdo = db();
        $role = "user";

        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
    
        $sql = 'INSERT INTO User(full_name, email, password, role, created_at, balance)
                VALUES(:full_name, :email, :password, :role, datetime("now"), :balance)';
        
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':full_name' => $fullname,    
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => $role,
            ':balance' => 800            
        ]);
        
        return $statement->rowCount() > 0;
        
    } catch(PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "<br>";
        if ($e->getCode() == 23000) {
            $_SESSION['errors']['email'] = 'This email is already registered';
        }
        return false;
    }
}

function find_user_by_full_name(string $full_name):bool|array {
       $sql = 'SELECT id, full_name, password, email, role, balance password
            FROM User
            WHERE full_name=:full_name';

    $statement = db()->prepare($sql);
    $statement->bindValue(':full_name', $full_name, PDO::PARAM_STR);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);

}
function find_user_by_email(string $email): array|bool 
{
    $pdo = db();

    $sql = 'SELECT id, full_name, password, email, role, balance , company_id FROM User WHERE email = :email';
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':email', $email, PDO::PARAM_STR);
    $statement->execute();
    
    return $statement->fetch(PDO::FETCH_ASSOC);
}
function login(string $email, string $password): bool
{
    $user = find_user_by_email($email); 

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id();

        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];  
        $_SESSION['role'] = $user['role'];
        $_SESSION['balance'] = $user['balance'];
        $_SESSION['company_id'] = $user['company_id'];

        return true;
    }
    return false;
}
function is_user_logged_in():bool {
    return isset($_SESSION['email']);
}

function require_login(): void 
{
    if (!is_user_logged_in()) {
        redirect_to('login');
    }
}

function logout(): void
{
    if (is_user_logged_in()) {
        unset($_SESSION['email'], $_SESSION['user_id']);
        session_destroy();
        redirect_to('login');
}}

function current_user()
{
    if (is_user_logged_in()) {

        return [
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'company_id'=> $_SESSION['company_id'],
            'balance' => $_SESSION['balance'],
            'id'  => $_SESSION['user_id']
        ];
    }
    return null;

}

function find_bus_routes($from_city_id,$to_city_id,$date){
    try{
        $pdo = db();

        $sql = 'SELECT * FROM Trips
                WHERE departure_city = :from_city
                AND destination_city = :to_city
                AND departure_time LIKE  :date_time
                AND capacity > 0
                ORDER BY departure_time';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':from_city'=> $from_city_id,
            ':to_city'=> $to_city_id,
            ':date_time'=> $date . '%'
        ]);
        return $stmt->fetchAll();
    }
catch (PDOException $e) {
    error_log("Bus routes error: " . $e->getMessage() );
    return [];}
}

function get_bus_company_by_id(int $company_id) {
    try {
        $pdo = db();

        $sql = 'SELECT 
                    id,
                    name,
                    logo_path,
                    created_at
                FROM Bus_Company 
                WHERE id = :company_id';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        error_log("Bus company by ID error: " . $e->getMessage());
        return null;
    }
}

function find_bus_routes_from_id(int $route_id){
    try{
        $pdo = db();

        $sql = 'SELECT * FROM Trips
                WHERE id = :routeid';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':routeid'=> $route_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);    }
    catch (PDOException $e) {
        error_log("Bus routes error: " . $e->getMessage() . $e->errorInfo()[2]);
        return false;}
}

function add_route(int $company_id, $destination_city, $arrival_time, $departure_time, $departure_city, $price,$capacity) {
    try {
        $pdo = db();

        $sql = "INSERT INTO Trips(company_id,destination_city,arrival_time,departure_time,departure_city,price,capacity,created_date)
                VALUES(:companyid,:destinationcity,:arrivaltime,:departuretime,:departurecity,:price,:capacity,datetime('now'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":companyid"=> $company_id,
            ":destinationcity"=> $destination_city,
            ":arrivaltime"=> $arrival_time,
            ":departuretime"=> $departure_time,
            ":departurecity"=> $departure_city,
            ":price"=> $price,
            ":capacity"=> $capacity
        ]);
    return $stmt->rowCount();
    }
catch (PDOException $e) {
            echo "❌ Database Error: " . $e->getMessage() . "<br>";
return false;
}
}

function find_bus_routes_from_company(int $company_id) {
    try {
        $pdo = db();
        $sql = "SELECT * FROM Trips
                WHERE company_id = :companyid";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":companyid"=> $company_id
            ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            return false;
        }
}

function delete_route(int $route_id, int $company_id) {
    try {
        $pdo = db();
        $sql = "DELETE FROM Trips WHERE id = :routeid AND company_id=:companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":routeid"=> $route_id,
            ":companyid"=> $company_id
            ]);
            return $stmt->rowCount();
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;

}
}

function add_coupon($code,$discount,$usage_limit,$expire_date, int $company_id) {
    try {
        $pdo = db();
        $sql = "INSERT INTO Coupons(code,discount,company_id,usage_limit,expire_date,created_at)
                VALUES (:fullcode,:fulldiscount,:companyid,:usagelimit,:expire,datetime('now'))";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":fullcode"=> $code,
            ":fulldiscount"=> $discount,
            ":companyid" => $company_id,
            ":usagelimit"=> $usage_limit,
            ":expire"=> $expire_date
        ]);
        return $stmt->rowCount();        
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function get_coupons_by_id(int $company_id){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Coupons WHERE company_id= :companyid AND usage_limit > 0 AND expire_date > datetime('now')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":companyid"=> $company_id
            ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function delete_coupon(int $coupon_id,int $company_id){
    try {
        $pdo = db();
        $sql = "DELETE FROM Coupons WHERE id=:coupon_id AND company_id=:companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":coupon_id"=> $coupon_id,
            ":companyid"=> $company_id
            ]);
            return $stmt->rowCount();
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function find_coupon_by_id(int $coupon_id,int $company_id){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Coupons WHERE id = :couponid  AND company_id = :companyid  AND usage_limit > 0 AND expire_date > datetime('now')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":couponid"=> $coupon_id,
            ":companyid"=> $company_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            return false;
        }}

function change_coupon($code,$discount,$usage_limit,$expire_date, int $company_id, int $coupon_id){
    try {  
        $pdo = db();
        $sql = "UPDATE Coupons
                SET code = :fullcode,
                    discount = :fulldiscount,
                    usage_limit = :usagelimit,
                    expire_date = :expire
                WHERE id = :couponid AND company_id = :companyid";

                 $stmt = $pdo->prepare($sql);

                 $stmt->execute([
            ":fullcode"=> $code,
            ":fulldiscount"=> $discount,
            ":companyid" => $company_id,
            ":usagelimit"=> $usage_limit,
            ":expire"=> $expire_date,
            ":couponid"=> $coupon_id
        ]);
        
        return $stmt->rowCount();
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            return false;
        }}

function find_route_by_id(int $route_id, int $company_id){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Trips WHERE id = :routeid  AND company_id = :companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":routeid"=> $route_id,
            ":companyid"=> $company_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            return false;
        }}

function change_route($from_city, $to_city, $departure_date, $arrival_date, $price, $capacity, int $route_id, int $company_id){
    try {
        $pdo = db();
        $sql = "UPDATE Trips
                SET destination_city=:tocity, arrival_time = :arrivaldate , departure_time=:departuredate , departure_city = :departurecity , price= :price , capacity = :capacity
                WHERE id=:routeid AND company_id=:companyid";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ":departurecity"=> $from_city,
                    ":tocity"=> $to_city,
                    ":departuredate"=> $departure_date,
                    ":arrivaldate"=> $arrival_date,
                    ":price"=> $price,
                    ":capacity"=> $capacity,
                    ":routeid"=> $route_id,
                    ":companyid"=> $company_id
                    ]);
                    return $stmt->rowCount();
}
    catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        return false;
    }}

function create_bus_company($company_name){
        try {
            $pdo = db();
            $sql = "INSERT INTO Bus_Company(name,created_at)
                    VALUES(:companyname,datetime('now'))";  
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":companyname"=> $company_name
                ]);
                return $stmt->rowCount();

    }
    catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        return false;
    }}

function find_companies(){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Bus_Company";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function delete_company(int $company_id){
    try {
        $pdo = db();
        $sql = "DELETE FROM Bus_Company WHERE id=:companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":companyid"=> $company_id
            ]);
        return $stmt->rowCount();
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function change_company_name( $company_name, int $company_id ){
    try {
        $pdo = db();
        $sql = "UPDATE Bus_Company SET name=:companyname WHERE id=:companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":companyname", $company_name, PDO::   PARAM_STR);
        $stmt->bindParam(":companyid", $company_id, PDO:: PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();

}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function change_company_path( int $company_id, $company_path ){try {
        $pdo = db();
        $sql = "UPDATE Bus_Company SET logo_path=:companypath WHERE id=:companyid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":companypath", $company_path, PDO::   PARAM_STR);
        $stmt->bindParam(":companyid", $company_id, PDO:: PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();

}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function get_company_id_by_name($company_name){
    try {
        $pdo = db();
        $sql = "SELECT id FROM Bus_Company WHERE name=:companyname ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":companyname", $company_name, PDO:: PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}}

function add_company_admin(string $fullname, string $email, string $password, string $company_name): bool
{
    try {
        $pdo = db();
        $role = "company";
        $company_id_data = get_company_id_by_name($company_name);
        
        if (!$company_id_data || !isset($company_id_data['id'])) {
            return false;
        }
        
        $company_id = $company_id_data['id']; 

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
    
        $sql = 'INSERT INTO User(full_name, email, password, role, created_at, balance , company_id)
                VALUES(:full_name, :email, :password, :role, datetime("now"), :balance ,:companyid)';
        
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':full_name' => $fullname,    
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => $role,
            ':balance' => 800,
            ':companyid' => $company_id           
        ]);
        
        return $statement->rowCount() > 0;
        
    } catch(PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "<br>";
        if ($e->getCode() == 23000) {
            $_SESSION['errors']['email'] = 'This email is already registered';
        }
        return false;
    }
}

function add_coupon_admin($code,$discount,$usage_limit,$expire_date) {
    try {
        $pdo = db();
        $sql = "INSERT INTO Coupons(code,discount,usage_limit,expire_date,created_at)
                VALUES (:fullcode,:fulldiscount,:usagelimit,:expire,datetime('now'))";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":fullcode"=> $code,
            ":fulldiscount"=> $discount,
            ":usagelimit"=> $usage_limit,
            ":expire"=> $expire_date
        ]);
        return $stmt->rowCount();        
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function get_all_coupons(){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Coupons";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
        $coupons = $stmt->fetchAll();
        return $coupons;        
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}


function delete_coupon_admin(int $coupon_id){
    try {
        $pdo = db();
        $sql = "DELETE FROM Coupons WHERE id=:coupon_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":coupon_id"=> $coupon_id
            ]);
            return $stmt->rowCount();
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    return false;
}}

function find_coupon_by_id_admin(int $coupon_id){
    try {
        $pdo = db();
        $sql = "SELECT * FROM Coupons WHERE id = :couponid ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":couponid"=> $coupon_id,
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            return false;
        }}

function get_taken_seats( int $trip_id ){
    try {
        $pdo = db();
        

        $sql = "SELECT T1.seat_number 
                FROM Booked_Seats AS T1
                JOIN Tickets AS T2 ON T1.ticket_id = T2.id
                WHERE T2.trip_id = :trip_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
        ":trip_id"=> $trip_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error retrieving taken seats: " . $e->getMessage());
    return [];
}
}

function create_ticket(int $ticket_id, int $user_id, int $total_price){
    try {
        $pdo = db();
        $sql = "INSERT INTO Tickets (trip_id,user_id,total_price,status,created_at)
                VALUES (:trip_id,:user_id,:total_price,'active',datetime('now'))";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":trip_id"=> $ticket_id,
            ":user_id"=> $user_id,
            ":total_price"=> $total_price
            ]);
        return $pdo->lastInsertId();
    }
    catch (PDOException $e) {
        error_log("Error". $e->getMessage());
        return false;
    }
}

function create_booked_seats( int $ticket_id, int $seat_number) {
    try {
        $pdo = db();
        $sql = "INSERT INTO Booked_Seats (ticket_id,seat_number,created_at)
                VALUES (:ticket_id,:seat_number,datetime('now'))";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":ticket_id"=> $ticket_id,
                ":seat_number"=> $seat_number
                ]);
            
return $stmt->rowCount() > 0;            
}
catch (PDOException $e) {
    error_log("Error". $e->getMessage());
    return false;
}}

function set_new_balance($id,$new_balance) {
    try {
        $pdo = db();
        $sql = 'UPDATE User SET balance=:new_balance WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'=> $id,
            ':new_balance'=> $new_balance
            ]);
        $_SESSION['balance'] = $new_balance;
        return $stmt->rowCount();
}
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}

function find_valid_coupon_by_code($code,$company_id){
    try {
        $pdo = db();
        $sql = 'SELECT * FROM Coupons WHERE code = :code AND usage_limit > 0 AND expire_date > datetime("now") AND (company_id IS NULL OR company_id = :company_id)  ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code'=> $code,
            ':company_id'=> $company_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}

function set_coupon_usage_limit($new_coupon_usage_limit , $id){
    try {
        $pdo = db();
        $sql = 'UPDATE Coupons SET usage_limit=:usage_limit WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usage_limit'=> $new_coupon_usage_limit,
            ':id'=> $id
            ]);
            return $stmt->rowCount();

}
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}

function create_user_coupon($coupon_id,$user_id){
    try {
        $pdo = db();
        $sql = 'INSERT INTO User_Coupons (coupon_id,user_id,created_at)
                VALUES (:coupon_id,:user_id,datetime("now"))';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':coupon_id'=> $coupon_id,
                    ':user_id'=> $user_id
                    ]);
                    return $stmt->rowCount();


}
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}

function check_user_coupons($coupon_id, $user_id){
    try {
        $pdo = db();
        $sql = 'SELECT * FROM User_Coupons WHERE coupon_id = :coupon_id AND user_id=:user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':coupon_id'=> $coupon_id,
            ':user_id'=> $user_id
            ]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return true; 
        } 
        
        return false; 
        
    }
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}

function get_tickets($user_id){
    try {
        $pdo = db();
        $sql = 'SELECT * FROM Tickets WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'=> $user_id
            ]);
        
        return  $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        error_log('Error'. $e->getMessage());
        return false;
}
}

function get_taken_seats_by_ticket_id($ticket_id){
    try {
        $pdo = db();
        $sql = 'SELECT seat_number FROM Booked_Seats WHERE ticket_id=:ticket_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'=> $ticket_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log('Error'. $e->getMessage());
            return false;
        }}

function delete_booked_seats($ticket_id){
    try {
        $pdo = db();
        $sql = 'DELETE FROM Booked_Seats WHERE ticket_id = :ticket_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'=> $ticket_id
            ]);
            return $stmt->rowCount();

    }
    catch (PDOException $e) {
        error_log('Error'. $e->getMessage());
        return false;
    }}

function get_ticket_by_ticket_id($ticket_id){
    try {
        $pdo = db();
        $sql = 'SELECT * FROM Tickets WHERE id=:ticket_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'=> $ticket_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        error_log('Error'. $e->getMessage());
        return false;
    }}

function cancel_ticket( $ticket_id ){
    try {
        $pdo = db();
        $sql = 'UPDATE Tickets SET status = "canceled" WHERE id = :ticket_id ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'=> $ticket_id
            ]);
            return $stmt->rowCount();
    }
    catch (PDOException $e) {
        error_log('Error'. $e->getMessage());
        return false;
    }}

function get_ticket_by_route_id(int $route_id): array
{
    try {
        $pdo = db();
        $sql = 'SELECT * FROM Tickets WHERE trip_id = :route_id'; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':route_id' => $route_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        
    } catch (PDOException $e) {
        error_log("Route ticket retrieval error: " . $e->getMessage());
        return []; 
    }
}

function find_user_by_id(int $user_id): array|bool {
    $pdo = db();
    $sql = 'SELECT id, full_name, email, role, balance FROM User WHERE id = :user_id';
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function update_ticket_expire(){
    try{
    $pdo = db();
    $sql = 'UPDATE Tickets SET status= "expired" WHERE status = "active" AND trip_id IN (SELECT id FROM Trips WHERE departure_time < datetime("now"))';
    $stmt = $pdo->prepare($sql);
    $stmt ->execute();

    return $stmt->rowCount();
}
catch (PDOException $e) {
    error_log('Error'. $e->getMessage());
    return false;
}}