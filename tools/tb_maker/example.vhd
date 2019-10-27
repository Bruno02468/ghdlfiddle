-- example testbench skeleton for an n-bit ALU
-- based on gjvnq's original, hand-made testbench
library ieee;
USE ieee.math_real.ceil;
USE ieee.math_real.log2;

use work.utils.all;
use work.alu;

entity tb is
end tb;

architecture tb of tb is
    	constant size : natural := 16;
      signal A, B : bit_vector (size-1 downto 0); -- inputs
      signal F : bit_vector (size-1 downto 0 ) ; -- output
      signal S : bit_vector (3 downto 0); -- op selection
      signal Z : bit; -- zero flag
      signal Ov : bit; -- overflow flag
      signal Co : bit; -- carry out
begin
      UUT : entity alu
      generic map (size => size)
      port map (
            A => A,
            B => B,
            F => F,
            S => S,
            Z => Z,
            Ov => Ov,
            Co => Co);
      test: process
      begin
-- SKELETON HERE
      end process;
end tb;
