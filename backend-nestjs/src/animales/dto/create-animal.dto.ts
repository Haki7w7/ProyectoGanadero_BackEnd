
import { IsString, IsNotEmpty, IsNumber, IsDateString, IsEnum } from 'class-validator';

export class CreateAnimalDto {
  @IsString()
  @IsNotEmpty()
  numeroArete: string;

  @IsNumber()
  @IsNotEmpty()
  razaId: number;

  @IsString()
  @IsNotEmpty()
  sexo: string;

  @IsDateString()
  @IsNotEmpty()
  fechaNacimiento: string;

  @IsString()
  @IsNotEmpty()
  estado: string;

  @IsNumber()
  @IsNotEmpty()
  potreroId: number;
}