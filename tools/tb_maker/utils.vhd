library ieee;
USE ieee.numeric_std.ALL;
use ieee.std_logic_1164.all;

package utils is

function bin (lvec: in bit) return string;
function bin (lvec: in std_logic_vector) return string;
function bin (lvec: in bit_vector) return string;
function hex (lvec: in std_logic_vector) return string;
function hex (lvec: in bit_vector) return string;
function op2str (op: in bit_vector(1 downto 0)) return string;

end package;


package body utils is
    function op2str (op: in bit_vector(1 downto 0)) return string is
    begin
        case op is
            when "00" => return "AND";
            when "01" => return "OR";
            when "10" => return "ADD";
            when "11" => return "SLT";
        end case;
    end function;

    function bin (lvec: in bit) return string is
    begin
        if (lvec = '1') then
            return "1";
        elsif (lvec = '0') then
            return "0";
        else
            return "?";
        end if;
    end function;

	function bin (lvec: in bit_vector) return string is
	begin
		return bin(To_StdLogicVector(lvec));
    end function;

    function hex (lvec: in bit_vector) return string is
	begin
		return hex(To_StdLogicVector(lvec));
    end function;

    function bin (lvec: in std_logic_vector) return string is
        variable text: string(lvec'length-1 downto 0) := (others => '9');
    begin
        for k in lvec'range loop
            case lvec(k) is
                when '0' => text(k) := '0';
                when '1' => text(k) := '1';
                when 'U' => text(k) := 'U';
                when 'X' => text(k) := 'X';
                when 'Z' => text(k) := 'Z';
                when '-' => text(k) := '-';
                when others => text(k) := '?';
            end case;
        end loop;
        return text;
    end function;

    function hex (lvec: in std_logic_vector) return string is
        variable text: string(lvec'length / 4 - 1 downto 0) := (others => '9');
        subtype halfbyte is std_logic_vector(4-1 downto 0);
    begin
        assert lvec'length mod 4 = 0
            report "hex() works only with vectors whose length is a multiple of 4"
            severity FAILURE;
        for k in text'range loop
            case halfbyte'(lvec(4 * k + 3 downto 4 * k)) is
                when "0000" => text(k) := '0';
                when "0001" => text(k) := '1';
                when "0010" => text(k) := '2';
                when "0011" => text(k) := '3';
                when "0100" => text(k) := '4';
                when "0101" => text(k) := '5';
                when "0110" => text(k) := '6';
                when "0111" => text(k) := '7';
                when "1000" => text(k) := '8';
                when "1001" => text(k) := '9';
                when "1010" => text(k) := 'A';
                when "1011" => text(k) := 'B';
                when "1100" => text(k) := 'C';
                when "1101" => text(k) := 'D';
                when "1110" => text(k) := 'E';
                when "1111" => text(k) := 'F';
                when others => text(k) := '!';
            end case;
        end loop;
        return "0x"&text;
    end function;

end package body;
