
import { IsString, IsNotEmpty, IsNumber, IsDateString, IsOptional, MaxLength, IsIn } from 'class-validator';

export class CreateAnimalDto {
  @IsString()
  @IsNotEmpty()
  @MaxLength(50)
  numero_arete: string;

  @IsNumber()
  @IsNotEmpty()
  raza_id: number;

  @IsString()
  @IsNotEmpty()
  @IsIn(['Macho', 'Hembra'], { message: 'El sexo debe ser Macho o Hembra.' })
  sexo: string;

  @IsOptional()
  @IsDateString()
  fecha_nacimiento?: string;

  @IsOptional()
  @IsString()
  @MaxLength(50)
  estado?: string;

  @IsNumber()
  @IsNotEmpty()
  potrero_id: number;
}